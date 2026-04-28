<?php

use PhpMx\Cif;
use PhpMx\Terminal;

/**
 * Criptografa uma string ou um conjunto de argumentos utilizando o motor Cif.
 * @param array<string> ...$content Texto ou termos que serão cifrados.
 */
return new class {

    function __invoke(array ...$content)
    {
        $content = implode(' ', $content);

        Terminal::echol(Cif::on($content));
    }
};
