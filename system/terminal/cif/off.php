<?php

use PhpMx\Cif;
use PhpMx\Terminal;

/** Descriptografa e exibe no terminal o conteúdo de uma string */
return new class {

    function __invoke($cif)
    {
        Terminal::echo(Cif::off($cif));
    }
};
