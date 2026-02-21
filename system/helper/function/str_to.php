<?php

if (!function_exists('strToCamelCase')) {

    /**
     * Converte uma string para o formato camelCase.
     * Remove acentos, caracteres especiais e normaliza a capitalização (ex: "Test string" -> "testString").
     * @param string $string
     * @return string
     */
    function strToCamelCase(string $string): string
    {
        $string = remove_accents($string);
        $string = preg_replace('/[^a-zA-Z0-9]+/', ' ', $string);
        $string = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $string);
        $string = array_filter(array_map(fn($v) => ucfirst(strtolower(trim($v))), $string), fn($v) => !is_blank($v));
        $string = implode('', $string);
        $string = lcfirst($string);
        return $string;
    }
}

if (!function_exists('strToKebabCase')) {

    /**
     * Converte uma string para o formato kebab-case (hifenizado).
     * @param string $string
     * @return string
     */
    function strToKebabCase(string $string): string
    {
        $string = remove_accents($string);
        $string = preg_replace('/[^a-zA-Z0-9]+/', ' ', $string);
        $string = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $string);
        $string = array_filter(array_map(fn($v) => strtolower(trim($v)), $string), fn($v) => !is_blank($v));
        $string = implode('-', $string);
        return $string;
    }
}

if (!function_exists('strToPascalCase')) {

    /**
     * Converte uma string para o formato PascalCase.
     * @param string $string
     * @return string
     */
    function strToPascalCase(string $string): string
    {
        $string = remove_accents($string);
        $string = preg_replace('/[^a-zA-Z0-9]+/', ' ', $string);
        $string = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $string);
        $string = array_filter(array_map(fn($v) => ucfirst(strtolower(trim($v))), $string), fn($v) => !is_blank($v));
        $string = implode('', $string);
        return $string;
    }
}

if (!function_exists('strToSnakeCase')) {

    /**
     * Converte uma string para o formato snake_case (sublinhado).
     * @param string $string
     * @return string
     */
    function strToSnakeCase(string $string): string
    {
        $string = remove_accents($string);
        $string = preg_replace('/[^a-zA-Z0-9]+/', ' ', $string);
        $string = preg_split('/(?<=[a-z0-9])(?=[A-Z])|\s+/', $string);
        $string = array_filter(array_map(fn($v) => strtolower(trim($v)), $string), fn($v) => !is_blank($v));
        $string = implode('_', $string);
        return $string;
    }
}
