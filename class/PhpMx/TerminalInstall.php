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
            Terminal::echo("promote: $pathFile [promoted]");
        } else {
            Terminal::echo("promote: $pathFile [ignored]");
        }
    }

    /** Cria um novo diretório no projeto atual */
    protected function createDir(string $pathDir)
    {
        if (!Dir::check($pathDir)) {
            Dir::create("$pathDir");
            Terminal::echo("create dir: $pathDir [created]");
        } else {
            Terminal::echo("create dir: $pathDir [ignored]");
        }
    }

    /** Cria um novo arquivo no projeto atual */
    protected function createFile(string $pathFile, array $contentLines)
    {
        if (!File::check($pathFile)) {
            File::create($pathFile, implode("\n", $contentLines));
            Terminal::echo("create file: $pathFile [created]");
        } else {
            Terminal::echo("create file: $pathFile [ignored]");
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
            Terminal::echo("block file: $blockName ($pathFile) [added]");
        } else {
            Terminal::echo("block file: $blockName ($pathFile) [ignored]");
        }
    }
}
