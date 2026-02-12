<?php

if (!function_exists('strToCamelCase')) {

    /**
     * Converte uma string para o formato camelCase.
     * Remove acentos, caracteres especiais e normaliza a capitalização (ex: "Test string" -> "testString").
     * @param string $str
     * @return string
     */
    function strToCamelCase(string $str): string
    {
        $str = remove_accents($str);
        $str = preg_replace('/[^a-zA-Z0-9]+/', ' ', $str);
        $str = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $str);
        $str = array_filter(array_map(fn($v) => ucfirst(strtolower(trim($v))), $str), fn($v) => !is_blank($v));
        $str = implode('', $str);
        $str = lcfirst($str);
        return $str;
    }
}

if (!function_exists('strToKebabCase')) {

    /**
     * Converte uma string para o formato kebab-case (hifenizado).
     * @param string $str
     * @return string
     */
    function strToKebabCase(string $str): string
    {
        $str = remove_accents($str);
        $str = preg_replace('/[^a-zA-Z0-9]+/', ' ', $str);
        $str = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $str);
        $str = array_filter(array_map(fn($v) => strtolower(trim($v)), $str), fn($v) => !is_blank($v));
        $str = implode('-', $str);
        return $str;
    }
}

if (!function_exists('strToPascalCase')) {

    /**
     * Converte uma string para o formato PascalCase.
     * @param string $str
     * @return string
     */
    function strToPascalCase(string $str): string
    {
        $str = remove_accents($str);
        $str = preg_replace('/[^a-zA-Z0-9]+/', ' ', $str);
        $str = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $str);
        $str = array_filter(array_map(fn($v) => ucfirst(strtolower(trim($v))), $str), fn($v) => !is_blank($v));
        $str = implode('', $str);
        return $str;
    }
}

if (!function_exists('strToSnakeCase')) {

    /**
     * Converte uma string para o formato snake_case (sublinhado).
     * @param string $str
     * @return string
     */
    function strToSnakeCase(string $str): string
    {
        $str = remove_accents($str);
        $str = preg_replace('/[^a-zA-Z0-9]+/', ' ', $str);
        $str = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $str);
        $str = array_filter(array_map(fn($v) => strtolower(trim($v)), $str), fn($v) => !is_blank($v));
        $str = implode('_', $str);
        return $str;
    }
}
