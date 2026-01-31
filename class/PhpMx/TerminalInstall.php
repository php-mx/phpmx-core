<?php

namespace PhpMx;

/** Dedicada a scripts de instalação de pacotes. */
abstract class TerminalInstall
{
    /** Promove um arquivo para o projeto atual */
    protected function promote(string $pathFile)
    {
        if (!File::check($pathFile)) {
            Terminal::run("promote $pathFile");
            Terminal::echo("[#green:promote] $pathFile");
        } else {
            Terminal::echo("[#green:promote] $pathFile [#whiteD:ignored]");
        }
    }

    /** Cria um novo diretório no projeto atual */
    protected function createDir(string $pathDir)
    {
        if (!Dir::check($pathDir)) {
            Dir::create("$pathDir");
            Terminal::echo("[#yellow:create] $pathDir");
        } else {
            Terminal::echo("[#yellow:create] $pathDir [#whiteD:ignored]");
        }
    }

    /** Cria um novo arquivo no projeto atual */
    protected function createFile(string $pathFile, array $contentLines)
    {
        if (!File::check($pathFile)) {
            File::create($pathFile, implode("\n", $contentLines));
            Terminal::echo("[#yellow:create] $pathFile");
        } else {
            Terminal::echo("[#yellow:create] [#] [#whiteD:ignored]", $pathFile);
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
            Terminal::echo("[#blue:block] $blockName $pathFile");
        } else {
            Terminal::echo("[#blue:block] $blockName $pathFile [#whiteD:ignored]");
        }
    }
}
