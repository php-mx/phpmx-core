<?php

use PhpMx\Cif;
use PhpMx\Terminal;

return new class {

    function __invoke($content)
    {
        $content = implode(' ', func_get_args());

        Terminal::echo(Cif::on($content));
    }
};
