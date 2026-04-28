<?php

use PhpMx\Cif;
use PhpMx\Terminal;

/**
 * Descriptografa e exibe no terminal o valor original de uma cifra.
 * @param string $cif A string cifrada.
 */
return new class {

    function __invoke(string $cif)
    {
        Terminal::echol(Cif::off($cif));
    }
};
