<?php

use PhpMx\Dir;
use PhpMx\File;
use PhpMx\Import;
use PhpMx\Log;
use PhpMx\Path;
use PhpMx\Terminal;

class mxInstall extends Terminal
{
    /** Promove um arquivo para o projeto atual */
    protected function promote(string $pathFile)
    {
        if (!File::check($pathFile)) {
            Terminal::run("promote $pathFile");
            self::echo("promote: $pathFile [promoted]");
        } else {
            self::echo("promote: $pathFile [ignored]");
        }
    }

    /** Cria um novo diretório no projeto atual */
    protected function createDir(string $pathDir)
    {
        if (!Dir::check($pathDir)) {
            Dir::create("$pathDir");
            self::echo("create dir: $pathDir [created]");
        } else {
            self::echo("create dir: $pathDir [ignored]");
        }
    }

    /** Cria um novo arquivo no projeto atual */
    protected function createFile(string $pathFile, array $contentLines)
    {
        if (!File::check($pathFile)) {
            File::create($pathFile, implode("\n", $contentLines));
            self::echo("create file: $pathFile [created]");
        } else {
            self::echo("create file: $pathFile [ignored]");
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
            self::echo("block file: $blockName ($pathFile) [added]");
        } else {
            self::echo("block file: $blockName ($pathFile) [ignored]");
        }
    }
}

return new class extends Terminal {

    function __invoke()
    {
        foreach (array_reverse(Path::seekForFiles('install')) as $installFile) {

            $origin = $this->getOrigim($installFile);

            if ($origin != 'CURRENT-PROJECT') {
                Log::add('mx', "Install [$origin]", function () use ($installFile, $origin) {
                    ob_start();
                    $script = require $installFile;
                    ob_end_clean();

                    if (is_extend($script, mxInstall::class)) {
                        $script();
                        self::echoLine();
                        self::echo("$origin installed");
                        self::echoLine();
                    }
                });
            }
        }

        Terminal::run('composer 1');
    }

    protected function getOrigim($path)
    {
        if ($path === 'install') return 'CURRENT-PROJECT';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }
};
