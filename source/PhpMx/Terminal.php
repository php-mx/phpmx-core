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

        if ($commandLine[0] == '--install') {
            $result = log_add('mx', 'install', [], function () {
                try {
                    return self::__install();
                } catch (Exception | Error $e) {
                    self::echo('Exception');
                    self::echo(' | [#]', $e->getMessage());
                    self::echo(' | [#] ([#])', [$e->getFile(), $e->getLine()]);
                    log_exception($e);
                    return false;
                }
            });
        } else {
            $result = log_add('mx', 'terminal [#]', [implode(' ', $commandLine)], function () use ($commandLine) {
                try {
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
                        throw new Exception("Command [$command] not fond");

                    $action = Import::return($commandFile);

                    if (!is_class($action, Terminal::class))
                        throw new Exception("Command [$command] not extends [" . static::class . "]");

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
                } catch (Exception | Error $e) {
                    self::echo('Exception');
                    self::echo(' | [#]', $e->getMessage());
                    self::echo(' | [#] ([#])', [$e->getFile(), $e->getLine()]);
                    log_exception($e);
                    return false;
                }
            });
        }

        if (env('DEV') && $showLog) {
            self::echo();
            self::echo(Log::getString());
        }

        return $result;
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

    /** Executa todos os scripts de instalação dos pacotes registrados */
    final static protected function __install()
    {
        $installs = Path::seekFiles('install');
        $installs = array_reverse($installs);

        foreach ($installs as $path) {
            $origin = 'unknown';
            if ($path == 'install') $origin = 'CURRENT-PROJECT';
            if (str_starts_with($path, 'vendor/')) {
                $parts = explode('/', $path);
                $origin = $parts[1] . '-' . $parts[2];
            }

            log_add('mx', 'install [#]', [$origin], function () use ($origin, $path) {
                self::echo('Installing [[#]]', $origin);

                ob_start();
                $action = require $path;
                ob_end_clean();

                if (!is_class($action, Terminal::class))
                    throw new Exception("file [$origin.install] not extends [" . static::class . "]");

                $action();
            });
        }

        self::echoLine();
        Terminal::run('composer 1');
        self::echo('Installation completed');
    }
}
