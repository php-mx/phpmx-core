<?php

use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista as bibliotecas registradas no projeto */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle('library', $filter);
    }

    protected function scan($path)
    {
        $files = [];
        foreach (Dir::seekForFile($path, true) as $ref)
            $files[] = ['ref' => $ref];
        return $files;
    }
};
