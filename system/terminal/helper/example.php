<?php

use PhpMx\Dir;
use PhpMx\Reflection\ReflectionExampleFile;
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
            '   [#c:p,#name] [[#typeKey]] [#c:sd,#file]',
            '   [#c:p,#name] [[#typeKey]] [#c:sd,#file]',
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
