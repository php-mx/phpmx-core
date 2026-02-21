<?php

namespace PhpMx\Trait;

use PhpMx\Dir;
use PhpMx\File;
use PhpMx\Import;
use PhpMx\Terminal;

/** @ignore */
trait TerminalInstallTrait
{
    /**
     * Copia um arquivo do sistema para o diretório local se ele ainda não existir.
     * @param string $pathFile Caminho do arquivo.
     */
    protected function promote(string $pathFile)
    {
        if (!File::check($pathFile)) {
            Terminal::run("promote $pathFile");
            Terminal::echol("[#c:s,promote] $pathFile");
        } else {
            Terminal::echol("[#c:sd,promote] [#c:dd,$pathFile]");
        }
    }

    /**
     * Cria um diretório no projeto se ele não for encontrado.
     * @param string $pathDir Caminho da pasta.
     */
    protected function createDir(string $pathDir)
    {
        if (!Dir::check($pathDir)) {
            Dir::create("$pathDir");
            Terminal::echol("[#c:s,create] $pathDir");
        } else {
            Terminal::echol("[#c:sd,create] [#c:dd,$pathDir]");
        }
    }

    /**
     * Cria um arquivo com o conteúdo fornecido (array de linhas).
     * @param string $pathFile Caminho do arquivo.
     * @param array $contentLines Linhas de conteúdo.
     */
    protected function createFile(string $pathFile, array $contentLines)
    {
        if (!File::check($pathFile)) {
            File::create($pathFile, implode("\n", $contentLines));
            Terminal::echol("[#c:s,create] $pathFile");
        } else {
            Terminal::echol("[#c:sd,create] [#c:dd,$pathFile]");
        }
    }

    /**
     * Insere um bloco de texto identificado por um comentário em um arquivo.
     * Evita duplicidade verificando a existência da tag "# blockName".
     * @param string $pathFile Destino.
     * @param string $blockName Identificador do bloco.
     * @param array $contentLines Conteúdo a ser inserido.
     */
    protected function blockFile(string $pathFile, string $blockName, array $contentLines)
    {
        $fileContent = Import::content($pathFile) ?? '';

        if (!str_contains($fileContent, "# $blockName")) {
            $fileContent = empty($fileContent) ? "# $blockName\n\n" : "$fileContent\n# $blockName\n\n";
            $fileContent .=  implode("\n", $contentLines);
            $fileContent .=  "\n";
            File::create($pathFile, $fileContent, true);
            Terminal::echol("[#c:s,block] [#c:p,$blockName] $pathFile");
        } else {
            Terminal::echol("[#c:sd,block] [#c:pd,$blockName] [#c:dd,$pathFile]");
        }
    }
}
