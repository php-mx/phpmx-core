<?php

use PhpMx\Json;
use PhpMx\Log;

if (!function_exists('cache')) {

    /**
     * Armazena e recupera o retorno de uma Closure em /library/cache.
     * @param string $cacheName Nome identificador do cache.
     * @param Closure $action Função que gera o valor caso o cache não exista ou esteja em DEV.
     * @return mixed Resultado processado ou recuperado do arquivo JSON.
     */
    function cache(string $cacheName, Closure $action): mixed
    {
        $cacheName = strToCamelCase($cacheName);
        return Log::add('cache', $cacheName, function () use ($cacheName, $action) {
            $file = path('library/cache', $cacheName);

            if (!env('USE_CACHE_FILE'))
                return $action();

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
                Json::export($file, [$result]);
            } catch (Throwable) {
            }

            return $result;
        });
    }
}

if (!function_exists('cacheTime')) {

    /**
     * Armazena e recupera o retorno de uma Closure em /library/cache por um período determinado.
     * @param string $cacheName Nome identificador do cache.
     * @param int $seconds Tempo de vida do cache em segundos.
     * @param Closure $action Função que gera o valor caso o cache tenha expirado.
     * @return mixed
     */
    function cacheTime(string $cacheName, int $seconds, Closure $action): mixed
    {
        $cacheName = strToCamelCase($cacheName) . '_time';

        return Log::add('cacheTime', $cacheName, function () use ($cacheName, $action, $seconds) {

            if (!env('USE_CACHE_FILE'))
                return $action();

            $file = path('library/cacheTime', $cacheName);

            $result = Json::import($file);

            if (is_array($result) && !empty($result)) {
                list($createdAt, $data) = $result;

                if (!env('DEV') && !is_null($data) && (microtime(true) - $createdAt) < $seconds)
                    return $data;
            }

            try {
                $data = $action();
            } catch (Throwable $e) {
                throw $e;
            }

            if (is_closure($data))
                return $data;

            try {
                Json::export($file, [microtime(true), $data]);
            } catch (Throwable) {
            }

            return $data;
        });
    }
}
