<?php

use PhpMx\File;
use PhpMx\Path;
use PhpMx\Terminal;

/**
 * Promove um arquivo do sistema (vendor) para o diretório local do projeto.
 * @param string file O caminho relativo do arquivo a ser promovido.
 * @example mx promote system/terminal/promote.php
 */
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

        Terminal::echol('File [#c:p,#] promoted to [#c:s,#]', [$current, $promoted]);
    }
};
