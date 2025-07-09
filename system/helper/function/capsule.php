<?php

if (!function_exists('encapsulate')) {

    /** Prepara uma variavel PHP para ser utilizada dentro do JS */
    function encapsulate($value): string
    {
        return addslashes(json_encode($value));
    }
}

if (!function_exists('decapsulate')) {

    /** Converte uma variavel JS para ser utilizada dentro do PHP */
    function decapsulate($value): ?array
    {
        return is_json($value) ? json_decode($value, true) : null;
    }
}
