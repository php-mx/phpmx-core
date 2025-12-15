<?php

use PhpMx\Cif;
use PhpMx\Terminal;

return new class {

    function __invoke($cif)
    {
        Terminal::echo(Cif::off($cif));
    }
};
