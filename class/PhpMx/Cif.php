<?php

namespace PhpMx;

use Exception;

/**
 * Classe utilitária para cifrar e decifrar variáveis de forma segura.
 * Utiliza certificados baseados em alfabetos embaralhados para ofuscação de dados serializados.
 */
abstract class Cif
{
    /** @ignore */
    protected static array $ENSURE;
    /** @ignore */
    protected static ?int $CURRENT_ID_KEY = null;
    /** @ignore */
    protected static ?array $CIF = null;

    final const BASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Converte uma variável em uma string cifrada.
     * @param mixed $var Variável de qualquer tipo para cifrar.
     * @param string|null $charKey Chave de caractere específica para forçar um índice de cifra.
     * @return string String cifrada delimitada por hífens.
     */
    static function on(mixed $var, ?string $charKey = null): string
    {
        self::__load();

        if (
            is_string($var)
            && str_starts_with($var, '-')
            && str_ends_with($var, '-')
            && self::checkEncapsChar(substr($var, 1, -1))
        ) return $var;

        $idKey = self::getUseIdKey($charKey);
        $var = serialize($var);
        $var = base64_encode($var);
        $var = str_replace('=', '', $var);
        $var = strrev($var);
        $var = self::replace($var, self::BASE, self::$CIF[$idKey]);
        $var = self::getEncapsChar($idKey) . $var . self::getEncapsChar($idKey, true);
        $var = str_replace('/', '-', $var);
        $var = "-$var-";

        return $var;
    }

    /**
     * Decifra uma string e retorna o valor original da variável.
     * @param mixed $var String cifrada para processamento.
     * @return mixed Valor original (deserializado) ou a própria variável caso não seja uma cifra válida.
     */
    static function off(mixed $var): mixed
    {
        if (!self::check($var)) return $var;

        if (strpos($var, ' ') !== false) $var = urlencode($var);

        $key = self::getUseIdKey(substr($var, 1, 1));
        $var = substr($var, 2, -2);
        $var = str_replace('-', '/', $var);
        $var = self::replace($var, self::$CIF[$key], self::BASE);
        $var = base64_decode(strrev($var));

        if (is_serialized($var))
            $var = unserialize($var);

        return $var;
    }

    /**
     * Verifica se uma variável atende aos requisitos estruturais para ser uma cifra MX.
     * @param mixed $var Variável para verificação.
     * @return bool
     */
    static function check(mixed $var): bool
    {
        return $var == self::on($var);
    }

    /**
     * Compara múltiplas variáveis para verificar se resultam na mesma cifra.
     * @param mixed $initial Valor base para comparação.
     * @param mixed ...$compare Outros valores para comparar.
     * @return bool
     */
    static function compare(mixed $initial, mixed ...$compare): bool
    {
        $initial = self::off($initial);

        foreach ($compare as $item)
            if ($initial != self::off($item))
                return false;

        return true;
    }

    /** @ignore */
    protected static function replace(string $string, string $in, string $out): string
    {
        for ($i = 0; $i < strlen($string); $i++)
            if (strpos($in, $string[$i]) !== false)
                $string[$i] = $out[strpos($in, $string[$i])];

        return $string;
    }

    /** @ignore */
    protected static function getUseIdKey(?string $charKey): int
    {
        self::__load();

        self::$CURRENT_ID_KEY = self::$CURRENT_ID_KEY ?? random_int(0, 61);

        if (!is_null($charKey))
            $idKey = array_flip(self::$ENSURE)[substr($charKey, 0, 1)];

        return $idKey ?? self::$CURRENT_ID_KEY;
    }

    /** @ignore */
    protected static function getEncapsChar(int $idKey, bool $reverse = false): string
    {
        if ($reverse) $idKey = 61 - $idKey;
        $charKey = self::$ENSURE[$idKey] ?? 0;
        return $charKey;
    }

    /** @ignore */
    protected static function checkEncapsChar(string $string)
    {
        $idCharKeyStart = self::getUseIdKey(substr($string, 0, 1));
        return self::getEncapsChar($idCharKeyStart, true) == substr($string, -1, 1);
    }

    /** @ignore */
    protected static function __load()
    {
        if (is_null(self::$CIF)) {
            $path = env('CIF');

            $path = Path::seekForFile("library/certificate/$path.crt");

            if (!$path)
                $path = Path::seekForFile('library/certificate/base.crt');

            self::loadFileCif($path);
        }
    }

    /** @ignore */
    private static function loadFileCif(string $path)
    {
        if (!File::check($path))
            throw new Exception("Cif file [$path] not found.");

        $content = Import::content($path);
        $content = str_replace([" ", "\t", "\n", "\r", "\0", "\x0B"], '', $content);
        $cif = str_split($content, 62);

        self::$ENSURE = str_split(array_pop($cif));
        self::$CIF = $cif;
    }
}
