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
        self::loadColors();

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

                self::echol('[#c:e,#] [#c:e,#]', [$type, $message]);
                self::echol(' [#]:[#]', [$file, $line]);
                foreach ($trace as $pos => $traceLine)
                    self::echol(' [#]:[#]', [$traceLine['file'], $traceLine['line']]);

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

    /** Exibe uma linha de texto no terminal com quebra de linha */
    static function echol(string $text = '', string|array $prepare = []): void
    {
        self::echo("$text\n", $prepare);
    }

    /** Exibe uma linha de texto no terminal */
    static function echo(string $text = '', string|array $prepare = [])
    {
        self::loadColors();
        $prepare = is_array($prepare) ? $prepare : [$prepare];
        echo prepare($text, [...self::$colors, ...$prepare]);
    }

    /** Solicita confirmação y/n do usuário */
    static function confirm(string $line = '', string|array $prepare = [], $default = null): bool
    {
        $input = '';

        while ($input != 'y' && $input != 'n') {
            self::echo("$line [#c:dd,(][#c:#styleY,#textY][#c:dd,/][#c:#styleN,#textN][#c:dd,):] ", [
                'styleY' => $default === true ? 'sub' : 's',
                'styleN' => $default === false ? 'eub' : 'e',
                'textY' => $default === true ? 'Y' : 'y',
                'textN' => $default === false ? 'N' : 'n',
                ...$prepare,
            ]);

            $input = strtolower(trim(fgets(STDIN)));

            usleep(250000);

            if ($input === '' && $default !== null) return $default;
        }

        return $input == 'y';
    }

    /** Solicita entrada de texto do usuário */
    static function input(string $label = '', string|array $prepare = [], string $default = '', bool $required = false): string
    {
        while (true) {
            $prompt = $label;

            if (!is_blank($default))
                $prompt .= " [#c:dd,(][#c:pd,#inputDefault][#c:dd,)]";

            $prompt .= "[#c:dd,:] ";

            self::echo($prompt, ['inputDefault' => $default, ...$prepare]);

            $input = trim(fgets(STDIN));

            usleep(250000);

            if (is_blank($input) && !is_blank($default))
                return $default;

            if (is_blank($input) && $required)
                continue;

            return $input;
        }
    }

    /** Solicita entrada de senha (texto oculto) */
    static function password(string $label = '', string|array $prepare = [], bool $required = false): string
    {
        while (true) {
            self::echo($label . "[#c:dd,:] ", $prepare);

            if (PHP_OS_FAMILY === 'Windows') {
                $command = 'powershell -Command "$password = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($password))"';
                $password = rtrim(shell_exec($command));
            } else {
                shell_exec('stty -echo');
                $password = trim(fgets(STDIN));
                shell_exec('stty echo');
                self::echol();
            }

            usleep(250000);

            if (is_blank($password) && $required)
                continue;

            return $password;
        }
    }

    /** Barra de progresso */
    static function progress(int $current, int $total, string $label = '', string $color = 'p')
    {
        $percent = ($current / $total);
        $barWidth = 33;
        $done = (int)($percent * $barWidth);
        $left = $barWidth - $done;

        $bar = str_repeat("█", $done) . str_repeat("░", $left);
        $p = ' ' . round($percent * 100);

        self::echo("\r[#] [#c:$color,#] [#]/[#][#]%", [
            $label,
            $bar,
            $current,
            $total,
            $p
        ]);

        if ($current === $total) self::echo("\n");
    }

    /** Solicita uma escolha de uma lista numerada */
    static function select(array $options, $default = null, bool $required = false): mixed
    {
        if (empty($options)) return null;

        $col = [];
        $size = [];
        $n = 0;

        foreach (array_values($options) as $key => $option) {
            $col[$n] = $col[$n] ?? [];
            $size[$n] = $size[$n] ?? 0;

            $key++;
            if ($key < 10 && count($options) > 10) $key =  " $key";

            $content = "[#c:p,$key)] $option";

            $size[$n] = max($size[$n], strlen($content));
            $col[$n][] = $content;

            if (count($col[$n]) >= 10) $n++;
        }

        $line = [];

        for ($i = 0; $i < 10; $i++) {
            $rowContent = "";
            foreach ($col as $columnIndex => $columnItems)
                if (isset($columnItems[$i])) {
                    $content = $columnItems[$i];
                    $padding = $size[$columnIndex] + 3;

                    $rowContent .= str_pad($content, $padding, " ");
                }
            if (!empty(trim($rowContent)))
                $line[] = $rowContent;
        }

        foreach ($line as $l)
            self::echol($l);

        self::echol();

        while (true) {
            self::echo("[#c:s,#][#c:s,#] ", ['(1-', count($options) . '):']);
            $input = trim(fgets(STDIN));
            usleep(250000);

            if ($input === '' && ($default !== null || !$required))
                return $default;

            $choiceIndex = intval($input) - 1;
            $optionsKeys = array_keys($options);
            if (isset($optionsKeys[$choiceIndex]))
                return $optionsKeys[$choiceIndex];
        }
    }

    /** Exibe uma tabela a partir de uma matriz */
    static function table(array $data, bool $hasHeader = true, string $color = 'p')
    {
        if (empty($data)) return;

        $widths = [];
        foreach ($data as $row)
            foreach (array_values($row) as $i => $value)
                $widths[$i] = max($widths[$i] ?? 0, mb_strlen("$value"));

        $separator = "+-" . implode("-+-", array_map(fn($w) => str_repeat("-", $w), $widths)) . "-+";
        $colorTag = "[#c:{$color}d,#]";

        self::echol($colorTag, [$separator]);

        foreach ($data as $index => $row) {
            $line = "[#c:{$color}d,|] ";
            $values = array_values($row);

            foreach ($values as $i => $v) {
                $space = $widths[$i] - mb_strlen($v);
                $v = $index === 0 && $hasHeader ? "[#c:{$color},$v]" : "$v";
                $line .= $v . str_repeat(" ", $space) . " [#c:{$color}d,|] ";
            }

            self::echol($line);

            if ($index === 0 && $hasHeader)
                self::echol($colorTag, [$separator]);
        }

        self::echol($colorTag, [$separator]);
    }

    private static function loadColors()
    {
        if (is_null(self::$colors))
            if (self::checkANSI()) {
                self::$colors = [
                    'c' => function ($style, $text = '') {
                        $codes = [];
                        $colors = ['p' => 36, 'd' => 0, 's' => 32, 'e' => 31, 'w' => 33];
                        $modifiers = ['b' => 1,  'i' => 3, 'u' => 4, 's' => 9, 'd' => 2];

                        $chars = str_split($style);
                        $codes[] = $colors[array_shift($chars)] ?? 0;

                        foreach ($chars as $c)
                            if (isset($modifiers[$c]))
                                $codes[] = $modifiers[$c];

                        return "\033[" . implode(';', $codes) . "m$text\033[0m";
                    },
                ];
            } else {
                self::$colors = ['c' => fn($style, $text = '') => $text];
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
