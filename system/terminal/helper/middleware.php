<?php

use PhpMx\DocScheme;
use PhpMx\Dir;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista as middlewares registradas no projeto */
return new class {

    use TerminalHelperTrait;

    function __invoke($fitler = null)
    {
        $this->handle(
            'system/middleware',
            $fitler,
            function ($item) {
                Terminal::echol(' - [#c:p,#ref] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
            }
        );
    }

    protected function scan($path)
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item)
            $items[] = DocScheme::docSchemeMiddlewareFile(path($path, $item));

        return $items;
    }
};
