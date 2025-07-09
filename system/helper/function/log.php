<?php

use PhpMx\Log;

if (!function_exists('log_add')) {

    /** Adicona ao log uma linha ou um escopo de linhas */
    function log_add(string $type, string $message, ?Closure $scope = null): mixed
    {
        if (is_null($scope)) return Log::add($type, $message);

        try {
            Log::add($type, $message, true);
            $result = $scope();
            Log::close();
            return $result;
        } catch (Throwable $e) {
            log_exception($e);
            Log::close();
            throw $e;
        }
    }
}

if (!function_exists('log_exception')) {

    /** Adiciona uma linha de exceção ao log */
    function log_exception(Throwable $e)
    {
        $type = is_class($e, Error::class) ? 'ERROR' : 'exception';
        $message = $e->getMessage();
        $file = path($e->getFile());
        $line = $e->getLine();

        Log::add($type, "$message $file ($line)");
    }
}
