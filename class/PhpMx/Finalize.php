<?php

namespace PhpMx;

use Closure;

/** Classe utilitária para finalizar execução de forma controlada */
abstract class Finalize
{
    protected static ?Closure $dieHandler = null;

    /** Define um handler customizado para die */
    static function setDieHandler(?Closure $handler): void
    {
        self::$dieHandler = $handler;
    }

    /** Finaliza a execução chamando handler customizado ou die nativo */
    static function die(mixed ...$params): never
    {
        if (self::$dieHandler) (self::$dieHandler)(...$params);
        die;
    }
}
