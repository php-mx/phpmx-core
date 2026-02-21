<?php

namespace PhpMx;

/**
 * Classe utilitária para gerenciamento de variáveis de ambiente.
 */
abstract class Env
{
    protected static array $DEFAULT = [];

    /**
     * Carrega variáveis de ambiente a partir de um arquivo de texto para o sistema.
     * @param string $filePath Caminho do arquivo (ex: .env).
     * @return bool True se o arquivo foi encontrado e processado.
     */
    static function loadFile(string $filePath): bool
    {
        $filePath = path($filePath);

        if (is_file($filePath)) {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') !== 0) {
                    list($name, $value) = explode('=', $line, 2);
                    self::set($name, $value);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Define o valor de uma variável de ambiente no escopo global $_ENV.
     * @param string $name Nome da variável.
     * @param mixed $value Valor a ser atribuído.
     * @return void
     */
    static function set(string $name, mixed $value): void
    {
        $name = trim($name);
        $value = trim($value, " \"'");

        $value = str_get_var($value);

        if (!isset($_ENV[$name]))
            $_ENV[$name] = $_ENV[$name] ?? $value;
    }

    /**
     * Recupera o valor de uma variável de ambiente ou o seu valor padrão.
     * @param string $name Nome da variável.
     * @return mixed O valor da variável ou null se não encontrada.
     */
    static function get(string $name): mixed
    {
        return $_ENV[$name] ?? self::$DEFAULT[$name] ?? null;
    }

    /**
     * Define um valor padrão para uma variável de ambiente caso ela não tenha sido declarada.
     * @param string $name Nome da variável.
     * @param mixed $value Valor padrão.
     * @return void
     */
    static function default(string $name, mixed $value): void
    {
        $value = str_get_var($value);

        self::$DEFAULT[$name] = $value;
    }
}
