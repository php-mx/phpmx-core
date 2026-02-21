<?php

use PhpMx\Mx5;

if (!function_exists('mx5')) {

    /**
     * Atalho para converter uma variável em um hash MX5 através da classe Mx5.
     * @param mixed $var Variável para codificação.
     * @return string O hash MX5 resultante.
     * @see \PhpMx\Mx5::on()
     */
    function mx5(mixed $var): string
    {
        return Mx5::on($var);
    }
}
