<?php

namespace PhpMx;

use Exception;
use ReflectionMethod;
use Throwable;

/**
 * Classe base para criação e execução de comandos de terminal.
 * Oferece suporte a estilização ANSI via tags de composição:
 * - Formato: `[#c:estilo,texto]` ou `[#c:estilo,#]` com prepare.
 * - Estilos (Cores): `p` (Primária), `s` (Sucesso), `e` (Erro), `w` (Alerta), `d` (Padrão).
 * - Modificadores: `b` (Negrito), `i` (Itálico), `u` (Sublinhado), `s` (Riscado).
 * @example self::echo("[#c:pb,Texto em negrito ciano]");
 * @example self::echol("[#c:e,#]", ["Mensagem de Erro"]);
 */
abstract class Terminal
{
    private static ?array $colors = null;

    /**
     * Executa uma linha de comando 
     * @example Terminal::run('make.command teste') Equivalente a php mx make.command teste
     * @example Terminal::run('make.command', 'teste') Equivalente a php mx make.command teste
     */
    final static function run()
    {
        self::loadColors();

        $commandLine = func_get_args();

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
                self::echol(' [#c:dd,#][#c:dd,:][#c:dd,#]', [$file, $line]);
                foreach ($trace as $traceLine)
                    self::echol(' [#c:dd,#][#c:dd,:][#c:dd,#]', [$traceLine['file'], $traceLine['line']]);

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

    /**
     * Exibe uma linha de texto no terminal com quebra de linha.
     * Aceita argumentos prepare para compor texto dinâmico
     * @param string $text Texto que deve ser exibido
     * @param array $prepare Dados prepare para compor o texto
     * @return void
     * @see \PhpMx\Prepare
     */
    static function echol(string $text = '', string|array $prepare = []): void
    {
        self::echo("$text\n", $prepare);
    }

    /**
     * Exibe uma linha de texto no terminal sem quebra de linha.
     * Aceita argumentos prepare para compor texto dinâmico
     * @param string $text Texto que deve ser exibido
     * @param array $prepare Dados prepare para compor o texto
     * @return void
     * @see \PhpMx\Prepare
     */
    static function echo(string $text = '', string|array $prepare = []): void
    {
        self::loadColors();
        $prepare = is_array($prepare) ? $prepare : [$prepare];
        echo prepare($text, [...self::$colors, ...$prepare]);
    }

    /**
     * Solicita confirmação do usuário (y/n)
     * @param string $text Mensagem de texto que deve ser exibida
     * @param array $prepare Dados prepare para compor o texto
     * @param boolean|null $default Valor retornado por padrão. Se não informado, o terminal vai entrar em loop ate receber um valor válido.
     * @return boolean 
     */
    static function confirm(string $text = '', string|array $prepare = [], ?bool $default = null): bool
    {
        $input = '';
        self::echol();

        while ($input != 'y' && $input != 'n') {
            self::echo("\e[1A\e[K");

            self::echo($text, $prepare);
            self::echo(" [#c:dd,(][#c:#styleY,#textY][#c:dd,/][#c:#styleN,#textN][#c:dd,):] ", [
                'styleY' => $default === true ? 'sub' : 's',
                'styleN' => $default === false ? 'eub' : 'e',
                'textY' => $default === true ? 'Y' : 'y',
                'textN' => $default === false ? 'N' : 'n'
            ]);

            $input = strtolower(trim(fgets(STDIN)));

            if ($input === '' && !is_null($default))
                $input = $default ? 'y' : 'n';
        }

        usleep(250000);

        return $input == 'y';
    }

    /**
     * Solicita entrada de texto do usuário
     * @param string $text Mensagem de texto que deve ser exibida
     * @param array $prepare Dados prepare para compor o texto
     * @param string|null $default Valor retornado por padrão.
     * @param boolean $required Se o terminal deve entrar em loop ate receber um valor válido.
     * @return string
     */
    static function input(string $text = '', string|array $prepare = [], ?string $default = null, bool $required = true): string
    {
        self::echol();

        while (true) {
            self::echo("\e[1A\e[K");

            self::echo($text, $prepare);

            $prompt = is_blank($default) ? "[#c:dd,:] " : " [#c:dd,(][#c:pd,$default][#c:dd,):] ";

            self::echo($prompt);

            $input = trim(fgets(STDIN));

            if (is_blank($input) && $required && is_null($default))
                continue;

            usleep(250000);

            if (is_blank($input) && !is_blank($default))
                return $default;

            return $input;
        }
    }

    /**
     * Solicita entrada de senha (texto oculto)
     * @param string $text Mensagem de texto que deve ser exibida
     * @param array $prepare Dados prepare para compor o texto
     * @param string|null $expected Valor experado para validação rápida
     * @param boolean $required Se o terminal deve entrar em loop ate receber o valor experado
     * @return string
     */
    static function password(string $text = '', string|array $prepare = [], ?string $expected = null, bool $required = true): string
    {
        self::echol();

        while (true) {
            self::echo("\e[1A\e[K");
            self::echo($text, $prepare);
            self::echo("[#c:dd,:] ");

            if (PHP_OS_FAMILY === 'Windows') {
                $command = 'powershell -Command "$password = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($password))"';
                $password = shell_exec($command);
                if ($password === null) die(self::echol());
                $password = rtrim($password, "\r\n");
            } else {
                shell_exec('stty -echo');
                $password = trim(fgets(STDIN));
                shell_exec('stty echo');
                self::echol();
            }

            if (is_blank($password) && $required)
                continue;

            if (!is_null($expected) && $password !== $expected)
                continue;

            usleep(250000);

            return $password;
        }
    }

    /**
     * Solicita uma escolha entre opções numeradas 
     * @param string $text Mensagem de texto que deve ser exibida
     * @param array $prepare Dados prepare para compor o texto
     * @param array $options Valores para composição da lista ['option'=>'value']
     * @param mixed $default Valor retornado por padrão.
     * @param bool $required
     * @return mixed Chave da opção escolhida no array $options
     */
    static function select(string $text = '', string|array $prepare = [], array $options = [], mixed $default = null, bool $required = true): mixed
    {
        if (empty($options)) return null;

        self::echo($text, $prepare);

        $col = [];
        $size = [];
        $n = 0;

        foreach (array_values($options) as $key => $option) {
            $col[$n] = $col[$n] ?? [];
            $size[$n] = $size[$n] ?? 0;
            $isDefault = $key === $default;
            $key++;
            $key = $key < 10 && count($options) > 10 ? " $key" : "$key";
            $content = $isDefault ? "[#c:p,$key)] $option*" : "[#c:p,$key)] $option";
            $size[$n] = max($size[$n], strlen("$option$key") + 10);
            $col[$n][] = $content;
            if (count($col[$n]) >= 10) $n++;
        }

        for ($i = 0; $i < 10; $i++) {
            $rowContent = "";
            foreach ($col as $columnIndex => $columnItems)
                if (isset($columnItems[$i])) {
                    $content = $columnItems[$i];
                    $padding = $size[$columnIndex] + 3;
                    $rowContent .= str_pad($content, $padding, " ");
                }
            if (!empty(trim($rowContent))) self::echol($rowContent);
        }

        self::echol();

        $return = null;
        while (is_null($return)) {
            self::echo("\e[1A\e[K");
            self::echo("[#c:dd,#][#c:dd,#] ", ['(1-', count($options) . '):']);
            $input = trim(fgets(STDIN));
            if ($input === '' && ($default !== null || !$required)) {
                $return = $default;
            } else {
                $choiceIndex = intval($input) - 1;
                $optionsKeys = array_keys($options);
                if (isset($optionsKeys[$choiceIndex]))
                    $return = $optionsKeys[$choiceIndex];
            }
        }

        usleep(250000);

        return $return;
    }

    /**
     * Exibe uma barra de progresso 
     * @param string text Mensagem de texto que deve ser exibida
     * @param string|array prepare Dados prepare para compor o texto
     * @param int $current Valor atual da barra
     * @param int $total Valor total da barra
     * @return void
     */
    static function progress(string $text = '', string|array $prepare = [], int $current = 0, int $total = 0): void
    {
        $percent = ($current / $total);
        $barWidth = 33;
        $done = (int)($percent * $barWidth);
        $left = $barWidth - $done;

        $bar = str_repeat("█", $done) . str_repeat("░", $left);
        $p = ' ' . round($percent * 100);

        self::echo("\r");
        self::echo($text, $prepare);
        self::echo(" [#c:pd,#] [#]/[#][#]%", [$bar, $current, $total, $p]);

        if ($current === $total) self::echo("\n");
    }

    /**
     * Exibe uma tabela a partir de uma matriz
     * @param array $data Dados da tabela
     * @param bool $hasHeader Se a primeira linha da tabela deve ser tratada como cabeçalho
     * @return void
     */
    static function table(array $data, bool $hasHeader = true)
    {
        if (empty($data)) return;

        $widths = [];
        foreach ($data as $row)
            foreach (array_values($row) as $i => $value)
                $widths[$i] = max($widths[$i] ?? 0, mb_strlen("$value"));

        $separator = "+-" . implode("-+-", array_map(fn($w) => str_repeat("-", $w), $widths)) . "-+";
        $colorTag = "[#c:pd,#]";

        self::echol($colorTag, [$separator]);

        foreach ($data as $index => $row) {
            $line = "[#c:pd,|] ";
            $values = array_values($row);

            foreach ($values as $i => $v) {
                $space = $widths[$i] - mb_strlen($v);
                $v = $index === 0 && $hasHeader ? "[#c:p,$v]" : "$v";
                $line .= $v . str_repeat(" ", $space) . " [#c:pd,|] ";
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
