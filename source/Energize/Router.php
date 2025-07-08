<?php

namespace Energize;

abstract class Router extends \PhpMx\Router
{
    /** Adiciona uma rota para responder por chamadas do tipo página GET e POST */
    static function page(string $route, string $response, array $middlewares = []): void
    {
        if (IS_TERMINAL || !IS_API) {
            self::get($route, $response, $middlewares);
            self::post($route, $response, $middlewares);
        };
    }
}
