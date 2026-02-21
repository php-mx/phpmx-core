<?php

if (!function_exists('phpex')) {

    /**
     * Verifica se uma extensão do PHP está ativa e carregada no servidor.
     * @param string $extension Nome da extensão (ex: 'mbstring', 'gd', 'xdebug').
     * @param bool $throw Se deve lançar uma exceção caso a extensão não esteja ativa.
     * @return bool True se a extensão estiver carregada.
     * @throws Exception Caso a extensão não exista e $throw seja true.
     */
    function phpex(string $extension, bool $throw = true): bool
    {
        $loaded = extension_loaded($extension);

        if (!$loaded && $throw)
            throw new Exception("Extension [$extension] is required.");

        return $loaded;
    }
}
