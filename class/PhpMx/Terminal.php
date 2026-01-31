<?php

namespace PhpMx;

use Exception;
use ReflectionMethod;
use Throwable;

/** Classe base para criação e execução de comandos de terminal. */
abstract class Terminal
{
    private static ?array $colors = null;

    /** Executa uma linha de comando */
    final static function run(...$commandLine)
    {
        if (count($commandLine) == 1)
            $commandLine = explode(' ', array_shift($commandLine));

        $showLog = false;

        $commandLine = array_map(fn($v) => trim($v), $commandLine);
        $commandLine = array_filter($commandLine, fn($v) => boolval($v));

        if (!empty($commandLine) && str_starts_with($commandLine[0], '+')) {
            $showLog = true;
            $commandLine[0] = substr($commandLine[0], 1);
            if (empty($commandLine[0])) unset($commandLine[0]);
        }

        if (empty($commandLine)) $commandLine = ['logo'];
        $result = Log::add('mx', 'terminal ' . implode(' ', $commandLine), function () use ($commandLine) {
            try {
                $command = array_shift($commandLine);
                $params = $commandLine;

                $commandFile = remove_accents($command);
                $commandFile = strtolower($commandFile);

                $commandFile = explode('.', $commandFile);
                $commandFile = array_map(fn($v) => strtolower($v), $commandFile);
                $commandFile = path('system/terminal', ...$commandFile);
                $commandFile = File::setEx($commandFile, 'php');

                $commandFile = Path::seekForFile($commandFile);

                if (!$commandFile)
                    throw new Exception("Command [$command] not found");

                $action = Import::return($commandFile);

                if (!is_object($action) || !is_callable($action))
                    throw new Exception("Command [$command] not is object callable");

                $reflection = new ReflectionMethod($action, '__invoke');

                $countParams = count($params);
                foreach ($reflection->getparameters() as $required) {
                    if ($countParams) {
                        $countParams--;
                    } elseif (!$required->isDefaultValueAvailable()) {
                        $name = $required->getName();
                        throw new Exception("Parameter [$name] is required in [$command]");
                    }
                }

                return $action(...$params);
            } catch (Throwable $e) {

                $message = $e->getMessage();
                $file = $e->getFile();
                $line = $e->getLine();
                $trace = $e->getTrace();
                $type = $e::class;

                self::echo('[#redB:#] [#red:#]', [$type, $message]);
                self::echo('[#]:[#whiteB:#]', [$file, $line]);
                foreach ($trace as $pos => $traceLine)
                    self::echo(' [#][#]:[#whiteB:#]', [str_repeat(' ', $pos), $traceLine['file'], $traceLine['line']]);

                Log::exception($e);
                return false;
            }
        });

        if (env('DEV') && $showLog) {
            self::echo();
            self::echo(Log::getString());
        }

        return $result;
    }

    /** Exibe uma linha de texto no terminal */
    static function echo(string $line = '', string|array $prepare = []): void
    {
        self::loadColors();
        $prepare = is_array($prepare) ? $prepare : [$prepare];
        echo prepare("$line\n", [...self::$colors, ...$prepare]);
    }

    private static function loadColors()
    {
        if (is_null(self::$colors)) {
            self::$colors = [
                'red' => fn($text) => "\033[0;31m$text\033[0m",
                'redB' => fn($text) => "\033[1;31m$text\033[0m",
                'redU' => fn($text) => "\033[4;31m$text\033[0m",
                'redI' => fn($text) => "\033[3;31m$text\033[0m",
                'redD' => fn($text) => "\033[2;31m$text\033[0m",

                'green' => fn($text) => "\033[0;32m$text\033[0m",
                'greenB' => fn($text) => "\033[1;32m$text\033[0m",
                'greenU' => fn($text) => "\033[4;32m$text\033[0m",
                'greenI' => fn($text) => "\033[3;32m$text\033[0m",
                'greenD' => fn($text) => "\033[2;32m$text\033[0m",

                'yellow' => fn($text) => "\033[0;33m$text\033[0m",
                'yellowB' => fn($text) => "\033[1;33m$text\033[0m",
                'yellowU' => fn($text) => "\033[4;33m$text\033[0m",
                'yellowI' => fn($text) => "\033[3;33m$text\033[0m",
                'yellowD' => fn($text) => "\033[2;33m$text\033[0m",

                'blue' => fn($text) => "\033[0;34m$text\033[0m",
                'blueB' => fn($text) => "\033[1;34m$text\033[0m",
                'blueU' => fn($text) => "\033[4;34m$text\033[0m",
                'blueI' => fn($text) => "\033[3;34m$text\033[0m",
                'blueD' => fn($text) => "\033[2;34m$text\033[0m",

                'magenta' => fn($text) => "\033[0;35m$text\033[0m",
                'magentaB' => fn($text) => "\033[1;35m$text\033[0m",
                'magentaU' => fn($text) => "\033[4;35m$text\033[0m",
                'magentaI' => fn($text) => "\033[3;35m$text\033[0m",
                'magentaD' => fn($text) => "\033[2;35m$text\033[0m",

                'cyan' => fn($text) => "\033[0;36m$text\033[0m",
                'cyanB' => fn($text) => "\033[1;36m$text\033[0m",
                'cyanU' => fn($text) => "\033[4;36m$text\033[0m",
                'cyanI' => fn($text) => "\033[3;36m$text\033[0m",
                'cyanD' => fn($text) => "\033[2;36m$text\033[0m",

                'white' => fn($text) => "\033[0;37m$text\033[0m",
                'whiteB' => fn($text) => "\033[1;37m$text\033[0m",
                'whiteU' => fn($text) => "\033[4;37m$text\033[0m",
                'whiteI' => fn($text) => "\033[3;37m$text\033[0m",
                'whiteD' => fn($text) => "\033[2;37m$text\033[0m",
            ];

            if (!self::checkANSI())
                foreach (array_keys(self::$colors) as $color)
                    self::$colors[$color] = fn($text) => $text;
        }
    }

    private static function checkANSI(): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') return true;

        if (function_exists('sapi_windows_vt100_support')) {
            sapi_windows_vt100_support(STDOUT, true);
            return true;
        }

        return false;
    }
}
