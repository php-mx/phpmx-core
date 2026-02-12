<?php

use PhpMx\DocScheme;
use PhpMx\Dir;
use PhpMx\ReflectionFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista todas as funções de helper registradas no sistema */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/helper/function',
            $filter,
            function ($item) {
                Terminal::echol(' - [#c:p,#name][#c:p,()] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
                foreach ($item['variations'] as $variations) {
                    $variations = explode(' ', $variations);
                    $variations = array_map(fn($v) => "[#c:dd,$v]", $variations);
                    $variations = implode("[#c:d,#sep]", $variations);
                    Terminal::echol("         [#][#c:d,(]{$variations}[#c:d,)]", [$item['name'], 'sep' => ', ']);
                }
            }
        );
    }

    protected function scan($path): array
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item)
            foreach (ReflectionFile::helperFile(path($path, $item)) as $scheme)
                if ($scheme['typeKey'] == 'function')
                    $items[] = $scheme;

        return $items;
    }
};
