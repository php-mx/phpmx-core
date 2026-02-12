<?php

use PhpMx\Cif;
use PhpMx\Terminal;

/**
 * Descriptografa e exibe no terminal o valor original de uma cifra MX.
 * @param string cif A string cifrada (incluindo os hífens) para decodificação.
 */
return new class {

    function __invoke($cif)
    {
        Terminal::echol(Cif::off($cif));
    }
};
