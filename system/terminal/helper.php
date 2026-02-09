<?php

use PhpMx\Autodoc;
use PhpMx\Dir;
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
                Terminal::echol(' - [#c:p,#ref] [#description]', $item);
                foreach ($item['variations'] as $variation)
                    Terminal::echol(' [#c:dd,php] mx [#][#c:dd,#]', [$item['ref'], $variation]);
            }
        );
    }

    protected function scan($path)
    {
        $commands = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = Autodoc::getDocSchemeFileCommand(path($path, $item));

            $variations = [''];
            foreach ($scheme['params'] ?? [] as $param) {
                $name = '<' . $param['name'] . '>';

                if (!$param['optional'])
                    $variations[0] .= " $name";

                if ($param['optional'])
                    $variations[] = end($variations) . " $name";
            }

            $commands[] = [
                'ref' => $scheme['ref'],
                'description' => str_replace("\n", ' ', $scheme['doc']['description'] ?? ''),
                'variations' => $variations,
            ];
        }

        return $commands;
    }
};
