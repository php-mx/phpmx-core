<?php

use PhpMx\Autodoc;
use PhpMx\Dir;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'class',
            $filter,
            function ($item) {
                dd($item);
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
            $items[] = Autodoc::docSchemeSourceFile(path($path, $item));

        return $items;
    }
};
