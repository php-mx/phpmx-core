<?php

if (!function_exists('dbug')) {

    /**
     * Realiza o var_dump de múltiplas variáveis com configurações otimizadas de profundidade e exibição.
     * @param mixed ...$params Variáveis para depuração.
     * @return void
     */
    function dbug(mixed ...$params): void
    {
        ini_set('xdebug.var_display_max_depth', '10');
        ini_set('xdebug.var_display_max_children', '256');
        ini_set('xdebug.var_display_max_data', '1024');

        foreach ($params as $param)
            var_dump($param);
    }
}

if (!function_exists('dd')) {

    /**
     * Exibe os dados das variáveis (dump) e encerra a execução do sistema (die).
     * @param mixed ...$params Variáveis para depuração.
     * @return void
     */
    function dd(mixed ...$params): void
    {
        dbug(...$params);
        die;
    }
}
