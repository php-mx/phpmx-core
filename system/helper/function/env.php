<?php

use PhpMx\Env;

if (!function_exists('env')) {

    /**
     * Recupera o valor de uma variável de ambiente através.
     * @param string $name Nome da variável de ambiente.
     * @return mixed O valor configurado ou null se não encontrada.
     * @see \PhpMx\Env::get()
     */
    function env(string $name): mixed
    {
        return Env::get($name) ?? null;
    }
}
