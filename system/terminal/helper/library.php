<?php

use PhpMx\Dir;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista todos os arquivos e recursos registrados no diretório library do projeto.
 * @param string filter Parte do nome ou caminho do arquivo para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'library',
            $filter,
            ' - [#c:p,#name] [#c:sd,#file]'
        );
    }

    protected function scan($path)
    {
        $files = [];

        foreach (Dir::seekForFile($path, true) as $file)
            $files[] = ['name' => $file, 'file' => path($path, $file)];

        return $files;
    }
};
