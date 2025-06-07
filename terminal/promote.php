<?php

use PhpMx\File;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke($file)
    {
        $current =  Path::seekFile($file);

        if (!$current) throw new Exception("File [$file] not found");

        $promoted = path($file);

        if (File::check($promoted)) throw new Exception("File [$promoted] already exists in the current project");

        File::copy($current, $promoted);
        self::echo('File [[#]] promoted to [[#]]', [$current, $promoted]);
    }
};
