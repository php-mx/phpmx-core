<?php

if (!function_exists('phpex')) {

    /** Verifica se uma extensão do PHP está ativa */
    function phpex(string $extension, bool $throw = true): bool
    {
        $loaded = extension_loaded($extension);

        if (!$loaded && $throw)
            throw new Exception("Extension [$extension] is required.", STS_INTERNAL_SERVER_ERROR);

        return $loaded;
    }
}
