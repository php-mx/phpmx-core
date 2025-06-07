<?php

namespace PhpMx;

use Error;
use Exception;
use ReflectionMethod;

abstract class Terminal
{
    /** Executa uma linha de comando */
    final static function run(...$commandLine)
    {
        $showLog = false;

        Log::add('_system', 'Executando comando [#]', implode(' ', $commandLine), true);
        try {
            $commandLine = array_map(fn($v) => trim($v), $commandLine);
            $commandLine = array_filter($commandLine, fn($v) => boolval($v));

            if (!empty($commandLine) && str_starts_with($commandLine[0], '+')) {
                $showLog = true;
                $commandLine[0] = substr($commandLine[0], 1);
                if (empty($commandLine[0])) unset($commandLine[0]);
            }

            if (empty($commandLine)) $commandLine = ['logo'];

            $command = array_shift($commandLine);
            $params = $commandLine;

            $commandFile = remove_accents($command);
            $commandFile = strtolower($commandFile);

            $commandFile = explode('.', $commandFile);
            $commandFile = array_map(fn($v) => strtolower($v), $commandFile);
            $commandFile = Path::format('terminal', ...$commandFile);
            $commandFile = File::setEx($commandFile, 'php');

            $commandFile = Path::seekFile($commandFile);

            if (!$commandFile)
                throw new Error("Command [$command] not fond");

            $action = Import::return($commandFile);

            if (!is_class($action, Terminal::class))
                throw new Error("Command [$command] not extends [" . static::class . "]");

            $reflection = new ReflectionMethod($action, '__invoke');

            $countParams = count($params);
            foreach ($reflection->getparameters() as $required) {
                if ($countParams) {
                    $countParams--;
                } elseif (!$required->isDefaultValueAvailable()) {
                    $name = $required->getName();
                    throw new Error("Parameter [$name] is required in [$command]");
                }
            }

            $result = $action(...$params);
        } catch (Exception | Error $e) {
            self::echo('ERROR');
            self::echo(' | [#]', $e->getMessage());
            self::echo(' | [#] ([#])', [$e->getFile(), $e->getLine()]);
            Log::add('error', '[#] [#] ([#])', [
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ]);
        }
        Log::close();

        if (env('DEV') && $showLog) {
            self::echo();
            self::echoLine();
            self::echo();
            self::echo(Log::getString());
        }

        return $result ?? false;
    }

    /** Exibe uma linha de texto no terminal */
    static function echo(string $line = '', string|array $prepare = []): void
    {
        echo Prepare::prepare("$line\n", $prepare);
    }

    /** Exibe uma linha de separação no terminal */
    static function echoLine(): void
    {
        self::echo('------------------------------------------------------------');
    }
}
