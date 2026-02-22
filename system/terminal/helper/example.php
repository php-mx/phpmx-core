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

    function __invoke($filter = null)
    {
        $this->handle(
            'storage/exemple',
            $filter,
            function ($item) {
                Terminal::echol('   [#c:p,#name] [#c:sd,#file]', $item);
                if ($item['summary'])
                    Terminal::echol("      [#summary]", $item);
            },
            function ($item) {
                Terminal::echol('   [#c:p,#name] [#c:sd,#file]', $item);
                if ($item['summary'])
                    Terminal::echol("      [#summary]", $item);
            },
        );
    }

    protected function scan($path): array
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = ReflectionExampleFile::scheme(path($path, $item));
            if (!empty($scheme))
                $items[] = $scheme;
        }

        return $items;
    }
};
