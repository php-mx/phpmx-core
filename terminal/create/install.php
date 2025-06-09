<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke()
    {
        $command = 'install';
        $commandFile = 'install';

        if (File::check($commandFile))
            throw new Exception("Installation script [$command] already exists in project");

        $template = Path::seekFile('storage/template/terminal/install.txt');

        File::create($commandFile, $template);

        self::echo('Installation script created successfully', $command);
    }
};
