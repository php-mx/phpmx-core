<?php

use PhpMx\Autodoc;
use PhpMx\Dir;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista todas as constantes helper registradas no sistema */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/helper/constant',
            $filter,
            function ($item) {
                Terminal::echol();
                Terminal::echol(' - [#c:p,#ref] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                Terminal::echol('     [#description]', $item);
            }
        );
    }

    protected function scan($path): array
    {
        $functions = [];
        foreach (Dir::seekForFile($path, true) as $item)
            foreach (Autodoc::getDocSchemeHelperFileConstants(path($path, $item)) as $scheme)
                $functions[] = [
                    'ref' => $scheme['ref'],
                    'description' => str_replace("\n", ' ', $scheme['doc']['description'] ?? ''),
                    'file' => $scheme['file'],
                    'line' => $scheme['line'],
                ];

        return $functions;
    }
};
