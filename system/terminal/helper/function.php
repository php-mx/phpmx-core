<?php

use PhpMx\Autodoc;
use PhpMx\Dir;
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
                Terminal::echol(' - [#c:p,#ref] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
                Terminal::echol();
                foreach ($item['variations'] as $variations) {
                    $variations = explode(' ', $variations);
                    $variations = array_map(fn($v) => "[#c:dd,$v]", $variations);
                    $variations = implode("[#c:d,#sep]", $variations);
                    Terminal::echol("         [#][#c:d,(]{$variations}[#c:d,)]", [$item['ref'], 'sep' => ', ']);
                }
            }
        );
    }

    protected function scan($path): array
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item)
            foreach (Autodoc::docSchemesFunctionFile(path($path, $item)) as $scheme) {
                $variations = [''];
                foreach ($scheme['params'] ?? [] as $param) {
                    $name = '$' . $param['name'];
                    if (!$param['optional'])
                        $variations[0] .= " $name";
                    if ($param['optional'])
                        $variations[] = end($variations) . " $name";
                }
                $scheme['variations'] = array_map(fn($v) => trim($v), $variations);
                $items[] = $scheme;
            }

        return $items;
    }
};
