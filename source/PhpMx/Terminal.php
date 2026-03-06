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
 * @see \PhpMx\Prepare
 */
abstract class Terminal
{
    /**
     * Executa uma linha de comando
     * @param mixed ...$commandLine Comando que deve ser executado
     * @example Terminal::run('make.command teste') Equivalente a php mx make.command teste
     * @example Terminal::run('make.command', 'teste') Equivalente a php mx make.command teste
     */
    final static function run(...$commandLine)
    {
        $commandLine = implode(' ', $commandLine);
        $commandLine = explode(' ', $commandLine);

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
                    } elseif (!$required->isDefaultValueAvailable() && !$required->isVariadic()) {
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
     * @param string $text Texto que deve ser exibido
     * @param array $prepare Dados prepare para compor o texto
     * @return void
     */
    static function echol(string $text = '', string|array $prepare = []): void
    {
        self::echo("$text\n", $prepare);
    }

    /**
     * Exibe uma linha de texto no terminal sem quebra de linha.
     * @param string $text Texto que deve ser exibido
     * @param array $prepare Dados prepare para compor o texto
     * @return void
     */
    static function echo(string $text = '', string|array $prepare = []): void
    {
        $prepare = is_array($prepare) ? $prepare : [$prepare];
        $prepare['c'] = fn($style, $text = '') => self::colorize($style, $text);
        echo prepare($text, $prepare);
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
            self::echo(" [#c:dd,(][#c:#styleY,Y][#c:dd,/][#c:#styleN,N][#c:dd,):] ", [
                'styleY' => $default === true ? 'su' : 's',
                'styleN' => $default === false ? 'eu' : 'e'
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
     * @return mixed Chave da opção escolhida no array $options
     */
    static function select(string $text = '', string|array $prepare = [], array $options = [], mixed $default = null): mixed
    {
        if (empty($options)) return null;

        $display = prepare($text, [...$prepare, 'c' => fn($style, $text) => $text]);

        $optionsShow = array_map(fn($option) => prepare($option, ['c' => fn($style, $text) => $text]), array_values($options));
        $optionsKeys = array_keys($options);

        $col = [];
        $size = [];
        $n = 0;

        foreach ($optionsShow as $index => $option) {
            $col[$n] = $col[$n] ?? [];
            $size[$n] = $size[$n] ?? 0;

            $numDisplay = $index + 1;
            $ident = count($options) >= 10 && $numDisplay < 10 ? ' ' : '';

            $itemDisplay = ($default === $optionsKeys[$index]) ? "[#c:du,$option]" : $option;
            $itemContent = "{$ident}[#c:pb,$numDisplay] $itemDisplay";

            $itemClean = remove_accents(prepare($itemContent, ['c' => fn($style, $text) => $text]));
            $size[$n] = max($size[$n], strlen($itemClean));

            $col[$n][] = $itemContent;

            if (count($col[$n]) >= 10) $n++;
        }


        for ($i = 0; $i < 10; $i++) {
            $rowContent = "";
            foreach ($col as $columnIndex => $columnItems) {
                if (isset($columnItems[$i])) {
                    $item = $columnItems[$i];
                    $itemClean = remove_accents(prepare($item, ['c' => fn($style, $text) => $text]));
                    $item .= str_repeat(' ', $size[$columnIndex] - strlen($itemClean) + 3);
                    $rowContent .= $item;
                }
            }
            if (!empty(trim($rowContent))) self::echol($rowContent);
        }

        self::echol('[#c:dd,#]', str_repeat('-', array_sum($size) + (count($size) * 2) + 2));

        self::echol();

        $return = null;
        while (is_null($return)) {
            self::echo("\e[1A\e[K");

            self::echo("$display: [#c:dd,(1-" . count($options) . ")] ");

            $input = trim(fgets(STDIN));

            if (!empty($input)) {
                if (is_numeric($input)) {
                    $choiceIndex = intval($input) - 1;
                    if (array_key_exists($choiceIndex, $optionsKeys)) {
                        $return = $optionsKeys[$choiceIndex];
                    }
                }
            } else if (!is_null($default) && isset($options[$default])) {
                $return = $default;
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

        $clsData = $data;
        foreach ($clsData as &$clsLine)
            foreach ($clsLine as &$clsRow)
                $clsRow = remove_accents(prepare($clsRow, ['c' => fn($style, $v) => $v]));

        $widths = [];
        foreach ($clsData as $row)
            foreach (array_values($row) as $i => $value)
                $widths[$i] = max($widths[$i] ?? 0, mb_strlen("$value"));

        $separator = "+-" . implode("-+-", array_map(fn($w) => str_repeat("-", $w), $widths)) . "-+";
        $colorTag = "[#c:dd,#]";

        self::echol($colorTag, [$separator]);

        foreach ($data as $index => $row) {
            $line = "[#c:dd,|] ";
            $values = array_values($row);
            $clsValues = array_values($clsData[$index]);

            foreach ($values as $i => $v) {
                $space = $widths[$i] - mb_strlen($clsValues[$i]);
                $v = $index === 0 && $hasHeader ? "[#c:p,$v]" : "$v";
                $line .= $v . str_repeat(" ", $space) . " [#c:dd,|] ";
            }

            self::echol($line);

            if ($index === 0 && $hasHeader)
                self::echol($colorTag, [$separator]);
        }

        self::echol($colorTag, [$separator]);
    }

    /**
     * Aplica escape ANSI ao texto conforme o estilo informado (cor + modificadores).
     * @param string $style Código de estilo (ex: 'pb' = primário + negrito).
     * @param string $text Texto a ser colorido.
     * @return string Texto com escape ANSI ou texto puro se ANSI não for suportado.
     */
    private static function colorize($style, $text = ''): string
    {
        if (!self::checkANSI()) return $text;

        $codes = [];
        $colors = ['p' => 36, 'd' => 0, 's' => 32, 'e' => 31, 'w' => 33];
        $modifiers = ['b' => 1,  'i' => 3, 'u' => 4, 's' => 9, 'd' => 2];

        $chars = str_split($style);
        $codes[] = $colors[array_shift($chars)] ?? 0;

        foreach ($chars as $c)
            if (isset($modifiers[$c]))
                $codes[] = $modifiers[$c];

        return "\033[" . implode(';', $codes) . "m$text\033[0m";
    }

    /**
     * Verifica se o ambiente suporta saída ANSI colorida.
     * @return bool True se ANSI for suportado, false caso contrário.
     */
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
