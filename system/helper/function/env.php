<?php

use PhpMx\Env;

if (!function_exists('env')) {

    /**
     * Atalho para recuperar o valor de uma variável de ambiente através da classe Env.
     * @param string $name Nome da variável de ambiente.
     * @return mixed O valor configurado ou null se não encontrada.
     * @see \PhpMx\Env::get()
     */
    function env(string $name): mixed
    {
        return Env::get($name) ?? null;
    }
}
