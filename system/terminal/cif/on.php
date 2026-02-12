<?php

use PhpMx\Cif;
use PhpMx\Terminal;

/**
 * Criptografa uma string ou um conjunto de argumentos utilizando o motor Cif.
 * @param string content Texto ou termos que serão cifrados.
 */
return new class {

    function __invoke($content)
    {
        $content = implode(' ', func_get_args());

        Terminal::echol(Cif::on($content));
    }
};
