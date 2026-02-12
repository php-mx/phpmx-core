<?php

namespace PhpMx;

/**
 * Classe utilitária para codificação e verificação com hash MX5.
 * O MX5 é uma representação ofuscada de um MD5, utilizando um alfabeto personalizado baseado em uma chave de segurança definida no ambiente.
 */
abstract class Mx5
{
    protected static ?array $KEY = null;
    protected static array $HEX_CHARS = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'a', 'b', 'c', 'd', 'e', 'f'];
    protected static array $MX_CHARS  = ['s', 'i', 'q', 'j', 'n', 'g', 'p', 'l', 'v', 'o', 'u', 'y', 't', 'w', 'r', 'h'];

    /**
     * Converte uma variável ou um MD5 comum para o formato MX5.
     * Se a variável não for uma string/MD5, ela será serializada antes da conversão.
     * @param mixed $var Variável, string ou hash MD5 para codificar.
     * @return string Hash MX5 resultante (34 caracteres, inicia com 'm' e termina com 'x').
     */
    static function on(mixed $var): string
    {
        if (!self::check($var)) {
            if (!is_md5($var)) $var = md5(is_stringable($var) ? "$var" : serialize($var));
            $var = str_replace(self::$HEX_CHARS, self::loadKey(), $var);
            $var = "m{$var}x";
        }

        return $var;
    }

    /**
     * Decodifica um hash MX5 de volta para o seu valor MD5 original.
     * Se o valor passado não for um MX5, ele será convertido em um antes da decodificação.
     * @param mixed $var Hash MX5 para decodificar.
     * @return string Hash MD5 original (32 caracteres hexadecimais).
     */
    static function off(mixed $var): string
    {
        if (!self::check($var))
            return self::off(self::on($var));

        $var = str_replace(self::loadKey(), self::$HEX_CHARS, substr($var, 1, -1));

        return $var;
    }

    /**
     * Verifica se uma variável string segue o padrão e o alfabeto de um hash MX5.
     * @param mixed $var Variável para verificação.
     * @return bool True se for um MX5 válido.
     */
    static function check(mixed $var): bool
    {
        return is_string($var)
            && strlen($var) === 34
            && strtolower($var) === $var
            && $var[0] === 'm'
            && $var[33] === 'x'
            && strspn(substr($var, 1, 32), implode('', self::$MX_CHARS)) === 32;
    }

    /**
     * Compara se múltiplas variáveis resultam no mesmo hash MX5.
     * Útil para validar senhas ou tokens sem expor o MD5 real no comparativo.
     * @param mixed $initial Valor base para comparação.
     * @param mixed ...$compare Outros valores para comparar com o inicial.
     * @return bool True se todos os valores corresponderem ao mesmo hash.
     */
    static function compare(mixed $initial, mixed ...$compare): bool
    {
        $initial = self::off($initial);

        foreach ($compare as $item)
            if ($initial != self::off($item))
                return false;

        return true;
    }

    private static function loadKey(): array
    {
        if (is_null(self::$KEY)) {
            $key = env('MX5_KEY');
            $key = md5($key);
            $key = str_replace(self::$HEX_CHARS,  self::$MX_CHARS, $key);
            $key .= implode('', self::$MX_CHARS);
            $key = array_keys(array_flip(str_split(strrev($key))));
            self::$KEY = $key;
        }

        return self::$KEY;
    }
}
