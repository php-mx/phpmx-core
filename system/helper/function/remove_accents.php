<?php

if (!function_exists('remove_accents')) {

    /**
     * Remove a acentuação e caracteres especiais de uma string utilizando um mapa de normalização.
     * @param string $string O texto original com acentos.
     * @return string O texto normalizado (sem acentos).
     */
    function remove_accents(string $string): string
    {
        return strtr($string, CHAR_NORMALIZER);
    }
}
