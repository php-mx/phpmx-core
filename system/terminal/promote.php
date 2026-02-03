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
            throw new Exception("File [$promoted] already exists in [current-project]");

        File::copy($current, $promoted);

        Terminal::echoln('File [#c:p,#] promoted to [#c:s,#]', [$current, $promoted]);
    }
};
