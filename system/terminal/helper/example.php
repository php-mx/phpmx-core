<?php

use PhpMx\Dir;
use PhpMx\Reflection\ReflectionExampleFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista todos os arquivos de exemplo do projeto.
 * @param string $filter Parte do nome do arquivo para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke(?string $filter = null)
    {
        $show = function ($item) {
            Terminal::echol('   [#c:p,#name] [#c:dd,#_type] [#c:sd,#_file]', $item);
            if (isset($item['description']))
                Terminal::echol("      [#]", array_shift($item['description']));
        };

        $this->handle(
            'storage/example',
            $filter,
            $show,
            $show,
        );
    }

    protected function scan(string $path): array
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item)
            $items[] = ReflectionExampleFile::scheme(path($path, $item));


        return array_filter($items);
    }
};
