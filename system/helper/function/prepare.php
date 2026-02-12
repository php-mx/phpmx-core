<?php

use PhpMx\Prepare;

if (!function_exists('prepare')) {

    /**
     * Processa uma string de template, substituindo as tags pelos valores fornecidos.
     * @param string|null $string O texto original contendo as tags de template.
     * @param array|string $prepare Os dados para substituição (array associativo ou valor único).
     * @return string O texto processado com as substituições aplicadas.
     * @see \PhpMx\Prepare::prepare()
     */
    function prepare(?string $string, array|string $prepare = []): string
    {
        return Prepare::prepare($string ?? '', $prepare);
    }
}
