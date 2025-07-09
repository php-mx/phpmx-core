<?php

use PhpMx\View;

if (!function_exists('view')) {

    /** Renderiza uma view e retorna seu conteúdo em forma de string */
    function view(string $ref, string|array $data = [], array $params = []): string
    {
        return View::render($ref, $data);
    }
}
