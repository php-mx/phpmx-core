<?php

use PhpMx\Terminal;

/** Orgulhosamente exibe a logo do PhpMx */
return new class {

    function __invoke()
    {
        Terminal::echol('[#c:p,   @@@  @@@@@@   @@@@@@@     @@@@    @@@@@  ]');
        Terminal::echol('[#c:p,  @@@@@@@@@@@@@@@@@@@@@@@     @@@@   @@@@   ]');
        Terminal::echol('[#c:p,  @@@@     @@@@@     @@@@      @@@@@@@@@    ]');
        Terminal::echol('[#c:p,  @@@@     @@@@      @@@@       @@@@@@      ]');
        Terminal::echol('[#c:p,  @@@@     @@@@      @@@@        @@@@@      ]');
        Terminal::echol('[#c:p,  @@@@     @@@@      @@@@       @@@@@@@     ]');
        Terminal::echol('[#c:p,  @@@@     @@@@      @@@@      @@@@  @@@@   ]');
        Terminal::echol('[#c:p,  @@@@     @@@@      @@@@    @@@@     @@@@  ]');
        Terminal::echol('[#c:p, @@@@@     @@@@      @@@@@  @@@@       @@@@ ]');
    }
};
