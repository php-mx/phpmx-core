<?php

use PhpMx\Mx5;

if (!function_exists('mx5')) {

    /** Retorna o MX5 de uma variável */
    function mx5(mixed $var): bool
    {
        return Mx5::on($var);
    }
}
