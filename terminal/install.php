<?php

use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke()
    {
        self::run('--install');
    }
};
