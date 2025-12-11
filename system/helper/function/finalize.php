<?php

use PhpMx\Finalize;

if (!function_exists('finalize')) {

    /** Finaliza a execução chamando handler customizado ou die nativo */
    function finalize(): never
    {
        Finalize::die(...func_get_args());
    }
}
