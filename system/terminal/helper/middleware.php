<?php

use PhpMx\Autodoc;
use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista as middlewares registradas no projeto */
return new class {

    use TerminalHelperTrait;

    function __invoke($fitler = null)
    {
        $this->handle('system/middleware', $fitler);
    }

    protected function scan($path)
    {
        $commands = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = Autodoc::getDocSchemeFileMiddleware(path($path, $item));

            $commands[] = [
                'ref' => $scheme['ref'],
                'description' => str_replace("\n", ' ', $scheme['doc']['description'] ?? '')
            ];
        }

        return $commands;
    }
};
