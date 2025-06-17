<?php

use PhpMx\Code;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke($cif)
    {
        self::echo(Code::off($cif));
    }
};
