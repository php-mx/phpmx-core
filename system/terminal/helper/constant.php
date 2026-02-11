<?php

use PhpMx\DocScheme;
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
                Terminal::echol(' - [#c:p,#ref] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
            }
        );
    }

    protected function scan($path): array
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item)
            foreach (DocScheme::docSchemeSourceFile(path($path, $item)) as $scheme)
                $items[] = $scheme;

        return $items;
    }
};
