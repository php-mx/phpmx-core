<?php

if (!function_exists('path')) {

    /** Formata um caminho de diretório */
    function path(): string
    {
        $path = array_values(func_get_args());
        $path = implode('/', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace_all('//', '/', $path);

        $currentPath = getcwd();
        $currentPath = str_replace('\\', '/', $currentPath);
        $currentPath = rtrim($currentPath, '/');

        if (str_starts_with($path, $currentPath))
            $path = substr($path, strlen($currentPath));

        $path = ltrim($path, '/');

        if (str_starts_with($path, './'))
            $path = substr($path, 2);

        $path = str_trim($path, '/', '/ ');
        $path = str_replace_all('//', '/', $path);

        return $path;
    }
}
