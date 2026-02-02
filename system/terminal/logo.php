<?php

use PhpMx\Terminal;

/** Orgulhosamente exibe a logo do PhpMx */
return new class {

    function __invoke()
    {
        Terminal::echo('[#blue:   @@@  @@@@@@   @@@@@@@  ][#cyan:   @@@@    @@@@@  ]');
        Terminal::echo('[#blue:  @@@@@@@%@@@@@@@@%@@@@@@ ][#cyan:    @@@@   @@@@   ]');
        Terminal::echo('[#blue:  @@@@     @@@@@     @@@@ ][#cyan:     @@@@@@@@@    ]');
        Terminal::echo('[#blue:  @@@@     @@@@      @@@@ ][#cyan:      @@@@@@      ]');
        Terminal::echo('[#blue:  @@@@     @@@@      %@@@ ][#cyan:       @@@@@      ]');
        Terminal::echo('[#blue:  @@@@     @@@@      @@@@ ][#cyan:      @@@@@@@     ]');
        Terminal::echo('[#blue:  @@@@     @@@@      @@@@ ][#cyan:     @@@@  @@@@   ]');
        Terminal::echo('[#blue:  @@@@     @@@@      @@@@ ][#cyan:   @@@@     @@@@  ]');
        Terminal::echo('[#blue: @@@@@     @@@@      @@@@@][#cyan:  @@@@       @@@@ ]');
    }
};
