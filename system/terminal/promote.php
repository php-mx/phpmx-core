<?php

use PhpMx\File;
use PhpMx\Path;
use PhpMx\Terminal;

/** Copia um arquivo do vendor para o projeto local permitindo a customização do código original */
return new class {

    function __invoke($file)
    {
        $current = Path::seekForFile($file);

        if (!$current)
            throw new Exception("File [$file] not found");

        $promoted = path($file);

        if (File::check($promoted) || $promoted == $current)
            throw new Exception("File [$promoted] already exists in [CURRENT PROJECT]");

        File::copy($current, $promoted);
        Terminal::echo('File [#blue:#] promoted to [#cyan:#]', [$current, $promoted]);
    }
};
