<?php

use PhpMx\Cif;
use PhpMx\Terminal;

/** Criptografa uma string ou um conjunto de argumentos */
return new class {

    function __invoke($content)
    {
        $content = implode(' ', func_get_args());

        Terminal::echol(Cif::on($content));
    }
};
