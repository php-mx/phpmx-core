<?php

use PhpMx\Dir;
use PhpMx\Reflection\ReflectionHelperFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista todas as helpers de constantes no sistema.
 * @param string $filter Nome ou parte do nome de uma constante para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/helper/constant',
            $filter,
            function ($item) {
                Terminal::echol('   [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
            }
        );
    }

    protected function scan($path): array
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item)
            foreach (ReflectionHelperFile::schemeConstants(path($path, $item)) as $scheme)
                $items[] = $scheme;

        return array_filter($items);
    }
};
