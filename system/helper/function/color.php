<?php

if (!function_exists('colorRGB')) {

    /**
     * Converte uma string de cor Hexadecimal (com ou sem #) em uma string RGB separada por vírgulas.
     * Suporta formatos de 6, 3 ou 1 caractere (ex: 'FF0000', 'F00', 'F').
     * @param string $color A cor em hexadecimal ou já em formato RGB.
     * @return string Valores RGB (ex: '255,0,0').
     */
    function colorRGB(string $color): string
    {
        if (count(explode(',', $color)) == 3)
            return $color;

        $color = str_replace('#', '', $color);
        $c = ['R' => '', 'G' => '', 'B' => ''];
        if (strlen($color) == 6) {
            list($c['R'], $c['G'], $c['B']) = str_split($color, 2);
        } elseif (strlen($color) == 3) {
            list($c['R'], $c['G'], $c['B']) = str_split($color, 1);
            foreach ($c as $var => $value)
                $c[$var] = str_repeat($value, 2);
        } elseif (strlen($color) == 1) {
            foreach ($c as $var => $value)
                $c[$var] = str_repeat($color, 2);
        }

        foreach ($c as $var => $value)
            $c[$var] = hexdec($value);

        return implode(',', $c);
    }
}

if (!function_exists('colorHex')) {

    /**
     * Converte uma string de cor RGB (separada por vírgulas) em Hexadecimal de 6 caracteres.
     * @param string $color Valores RGB (ex: '255,0,0') ou hexadecimal.
     * @return string Cor em Hexadecimal sem o caractere # (ex: 'ff0000').
     */
    function colorHex(string $color): string
    {
        if (strpos($color, ',') === false)
            return str_replace('#', '', $color);

        $color = explode(',', $color);
        $r = array_shift($color) ?? '225';
        $g = array_shift($color) ?? '225';
        $b = array_shift($color) ?? '225';

        return str_pad(dechex($r), 2, 0) . str_pad(dechex($g), 2, 0) . str_pad(dechex($b), 2, 0);
    }
}
