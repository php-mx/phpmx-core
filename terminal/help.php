<?php

use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke()
    {
        Terminal::run('help.command');
    }
};
