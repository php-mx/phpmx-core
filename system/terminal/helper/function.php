<?php

use PhpMx\Autodoc;
use PhpMx\Dir;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista todas as funções de helper registradas no sistema */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle('system/helper/function', $filter);
    }

    protected function scan($path): array
    {
        $functions = [];
        foreach (Dir::seekForFile($path, true) as $item)
            foreach (Autodoc::getDocSchemeHelperFileFunctions(path($path, $item)) as $scheme)
                $functions[] = [
                    'ref' => $scheme['ref'],
                    'description' => str_replace("\n", ' ', $scheme['doc']['description'] ?? '')
                ];

        return $functions;
    }
};
