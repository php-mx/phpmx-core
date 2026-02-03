<?php

namespace PhpMx\Trait;

use PhpMx\Dir;
use PhpMx\File;
use PhpMx\Import;
use PhpMx\Terminal;

/** Dedicada a scripts de instalação de pacotes. */
trait TerminalInstallTrait
{
    /** Promove um arquivo para o projeto atual */
    protected function promote(string $pathFile)
    {
        if (!File::check($pathFile)) {
            Terminal::run("promote $pathFile");
            Terminal::echoln("[#c:s,promote] $pathFile");
        } else {
            Terminal::echoln("[#c:sd,promote] [#c:dd,$pathFile]");
        }
    }

    /** Cria um novo diretório no projeto atual */
    protected function createDir(string $pathDir)
    {
        if (!Dir::check($pathDir)) {
            Dir::create("$pathDir");
            Terminal::echoln("[#c:s,create] $pathDir");
        } else {
            Terminal::echoln("[#c:sd,create] [#c:dd,$pathDir]");
        }
    }

    /** Cria um novo arquivo no projeto atual */
    protected function createFile(string $pathFile, array $contentLines)
    {
        if (!File::check($pathFile)) {
            File::create($pathFile, implode("\n", $contentLines));
            Terminal::echoln("[#c:s,create] $pathFile");
        } else {
            Terminal::echoln("[#c:sd,create] [#c:dd,$pathFile]");
        }
    }

    /** Adiciona um bloco de conteúdo a um arquivo */
    protected function blockFile(string $pathFile, string $blockName, array $contentLines)
    {
        $fileContent = Import::content($pathFile) ?? '';

        if (!str_contains($fileContent, "# $blockName")) {
            $fileContent = empty($fileContent) ? "# $blockName\n\n" : "$fileContent\n# $blockName\n\n";
            $fileContent .=  implode("\n", $contentLines);
            $fileContent .=  "\n";
            File::create($pathFile, $fileContent, true);
            Terminal::echoln("[#c:s,block] [#c:p,$blockName] $pathFile");
        } else {
            Terminal::echoln("[#c:sd,block] [#c:pd,$blockName] [#c:dd,$pathFile]");
        }
    }
}
