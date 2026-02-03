<?php

use PhpMx\Terminal;

/** Orgulhosamente exibe a logo do PhpMx */
return new class {

    function __invoke()
    {
        Terminal::echoln('[#c:p,   @@@  @@@@@@   @@@@@@@     @@@@    @@@@@  ]');
        Terminal::echoln('[#c:p,  @@@@@@@@@@@@@@@@@@@@@@@     @@@@   @@@@   ]');
        Terminal::echoln('[#c:p,  @@@@     @@@@@     @@@@      @@@@@@@@@    ]');
        Terminal::echoln('[#c:p,  @@@@     @@@@      @@@@       @@@@@@      ]');
        Terminal::echoln('[#c:p,  @@@@     @@@@      @@@@        @@@@@      ]');
        Terminal::echoln('[#c:p,  @@@@     @@@@      @@@@       @@@@@@@     ]');
        Terminal::echoln('[#c:p,  @@@@     @@@@      @@@@      @@@@  @@@@   ]');
        Terminal::echoln('[#c:p,  @@@@     @@@@      @@@@    @@@@     @@@@  ]');
        Terminal::echoln('[#c:p, @@@@@     @@@@      @@@@@  @@@@       @@@@ ]');
    }
};
