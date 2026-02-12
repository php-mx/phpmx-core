<?php

use PhpMx\Dir;
use PhpMx\ReflectionFile;
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
                Terminal::echol(' - [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
            }
        );
    }

    protected function scan($path): array
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item)
            foreach (ReflectionFile::helperFile(path($path, $item)) as $scheme)
                if ($scheme['typeKey'] == 'environment')
                    $items[] = $scheme;

        return $items;
    }
};
