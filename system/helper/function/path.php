<?php

use PhpMx\Path;

if (!function_exists('path')) {

    /**
     * Formata e normaliza um caminho de diretório a partir de um ou mais segmentos.
     * @param string ...$segments Segmentos do caminho (ex: 'pasta', 'sub', 'arquivo.php').
     * @return string Caminho normalizado e limpo.
     * @see \PhpMx\Path::format()
     */
    function path(): string
    {
        return Path::format(...func_get_args());
    }
}
