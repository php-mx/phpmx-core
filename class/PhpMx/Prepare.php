<?php

namespace PhpMx;

/**
 * Classe utilitária para substituição de templates em textos. 
 * @example Prepare::prepare("Olá [#user.name]", ['user' => ['name' => 'Danilo']]) -> "Olá Danilo"
 * @example Prepare::prepare("Soma: [#sum:10,20]", ['sum' => fn($a, $b) => $a + $b]) -> "Soma: 30"
 * @example Prepare::prepare("Valor: [#]", [100]) -> "Valor: 100"
 */
abstract class Prepare
{
    /**
     * Prepara um texto substituindo ocorrências do template pelos dados fornecidos.
     * Aceita:
     * - Sequencial: `[#]`
     * - Referência: `[#key]` ou `[#user.name]` (Dot Notation)
     * - Funções: `[#key:param1,param2]` (Executa closures no array de dados)
     * @param string|null $string Texto base contendo as tags.
     * @param array|string $prepare Dados para substituição.
     * @return string Texto processado.
     */
    static function prepare(?string $string, array|string $prepare = []): string
    {
        if (!empty($prepare)) {
            $string = strval($string ?? '');
            if (!is_blank($string)) {
                $tags = self::getPrepareTags($string);
                if (!empty($tags)) {
                    $prepare = self::combinePrepare($prepare);
                    $string = self::resolve($string, $tags, $prepare);
                }
            }
        }
        return $string;
    }

    /** 
     * Retorna as tags prepare existentes em uma string (sem os colchetes).
     * @param string $string
     * @return array Lista de tags únicas encontradas.
     */
    static function tags($string): array
    {
        $tags = self::getPrepareTags($string);
        $tags = array_map(fn($v) => substr($v, 2, -1), $tags);
        return array_unique($tags);
    }

    /** 
     * Retorna as chaves disponíveis em um array de prepare processado.
     * @param array|string $prepare
     * @return array Lista de chaves (incluindo dot notation de subarrays).
     */
    static function keys($prepare): array
    {
        $keys = self::combinePrepare($prepare);
        return array_keys($keys);
    }

    /** 
     * Escapa as tags prepare para evitar que sejam processadas.
     * @param string $string Texto original.
     * @param array|null $prepare Se informado, escapa apenas chaves específicas.
     * @return string Texto escapado.
     */
    static function scape($string, ?array $prepare = null): string
    {
        if ($prepare) {
            $prepare = self::combinePrepare($prepare);
            $prepare = array_keys($prepare);

            $replace = array_map(fn($value) => "[&#35$value]", $prepare);
            $prepare = array_map(fn($value) => "[#$value]", $prepare);

            return str_replace($prepare, $replace, $string);
        } else {
            return str_replace('[#', "[&#35", $string);
        }
    }

    /** @ignore */
    protected static function resolve($string, $tags, $prepare): string
    {
        list($ppN, $ppR) = self::separePrepare($prepare);

        foreach ($tags as $tag) {
            $tag = substr($tag, 1, -1);
            $value = self::getTagValue($tag, $ppN, $ppR) ?? "[%$tag]";
            $string = str_replace_first("[$tag]", $value, $string);
        }

        $string = str_replace("[%#", '[#', $string);
        $string = str_replace('\#', "&#35", $string);

        return $string;
    }

    /** @ignore */
    protected static function getTagValue($tag, &$ppN, $ppR, bool $runClosure = true): mixed
    {
        if ($tag == '#') {
            $value = array_shift($ppN) ?? null;
            if ($runClosure && is_closure($value))
                $value = $value();
            return $value;
        }

        if (strpos($tag, ':') === false) {
            $tag = substr($tag, 1);
            $value = $ppR[$tag] ?? null;
            if ($runClosure && is_closure($value))
                $value = $value();
            return $value;
        } else {
            $paramns = explode(":", $tag);
            $function = array_shift($paramns);
            $paramns = implode(":", $paramns);
            $paramns = explode(",", $paramns);

            $function = self::getTagValue($function, $ppN, $ppR, false);

            if (is_closure($function)) {
                foreach ($paramns as &$param) {
                    if (intval($param) == $param) {
                        $param = intval($param);
                    } else if (strtolower($param) == 'false') {
                        $param = false;
                    } else if (strtolower($param) == 'true') {
                        $param = true;
                    } else {
                        $param = str_replace('\#', "&#35", $param);
                        $param = self::getTagValue($param, $ppN, $ppR) ?? $param;
                    }
                }
                return $function(...$paramns);
            }
            return null;
        }
    }

    /** @ignore */
    protected static function separePrepare($prepare): array
    {
        $sequence = [];
        $reference = [];
        foreach ($prepare as $key => $value) {
            if (is_numeric($key)) {
                $sequence[] = $value;
            } else {
                $reference[$key] = $value;
            }
        }
        return [$sequence, $reference];
    }

    /** @ignore */
    protected static function combinePrepare(array|string $prepare): array
    {
        $prepare = is_array($prepare) ? $prepare : [$prepare];
        foreach ($prepare as $key => $value) {
            if (is_array($value)) {
                $prepare[$key] = json_encode($value);
                foreach (self::combinePrepare($value) as $subKey => $subValue) {
                    $newKey = $subKey == '.' ? $key : "$key.$subKey";
                    $prepare[$newKey] = $subValue;
                }
            }
        }
        return $prepare;
    }

    /** @ignore */
    protected static function getPrepareTags(string $string): array
    {
        preg_match_all("#\[[\#\>][^\]]*+\]#i", $string, $tags);
        $tags = array_shift($tags);
        return $tags;
    }
}
