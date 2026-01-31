<?php

use PhpMx\Terminal;

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
