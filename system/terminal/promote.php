<?php

use PhpMx\File;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke($file)
    {
        $current = path(CORE_PATH, $file);

        if (!File::check($current))
            throw new Exception("File [$file] not found in phpmx");

        $promoted = path($file);

        if (File::check($promoted))
            throw new Exception("File [$promoted] already exists in current project");

        File::copy($current, $promoted);
        self::echo('File [[#]] promoted to [[#]]', [$current, $promoted]);
    }
};
