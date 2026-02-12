<?php

use PhpMx\Dir;
use PhpMx\ReflectionFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista e detalha todos os comandos disponíveis no terminal identificando parâmetros e origens */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/terminal',
            $filter,
            function ($item) {
                Terminal::echol(' - [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
                foreach ($item['variations'] as $variation)
                    Terminal::echol('         [#c:dd,php] mx [#][#c:dd,#]', [$item['name'], $variation]);
            }
        );
    }

    protected function scan($path)
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = ReflectionFile::commandFile(path($path, $item));
            if (!empty($scheme)) {
                $variations = [''];

                foreach ($scheme['params'] ?? [] as $param) {
                    $name = '<' . $param['name'] . '>';
                    if (!$param['optional'])
                        $variations[0] .= " $name";
                    if ($param['optional'])
                        $variations[] = end($variations) . " $name";
                }

                $scheme['variations'] = $variations;

                $items[] = $scheme;
            }
        }

        return $items;
    }
};
