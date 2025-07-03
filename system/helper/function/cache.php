<?php

use PhpMx\Json;

if (!function_exists('cache')) {

    /** Armazena e recupera o retorno de uma Closure em /storage/cache */
    function cache(string $cacheName, Closure $action): mixed
    {
        $file = path('storage/cache', strToCamelCase($cacheName));

        $result = Json::import($file);

        if (!env('DEV') && !empty($result))
            return array_shift($result);

        try {
            $result = $action();
        } catch (Throwable $e) {
            throw $e;
        }

        if (is_closure($result))
            return $result;

        try {
            Json::export([$result], $file);
        } catch (Throwable) {
        }

        return $result;
    }
}
