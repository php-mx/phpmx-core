<?php

use PhpMx\Code;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke($content)
    {
        $content = implode(' ', func_get_args());

        self::echo(Code::on($content));
    }
};
