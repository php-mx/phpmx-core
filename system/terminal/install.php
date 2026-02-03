<?php

use PhpMx\Log;
use PhpMx\Path;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalInstallTrait;

/** Executa os scripts de instalação de pacotes externos e atualiza o autoload do composer */
return new class {

    function __invoke()
    {
        foreach (array_reverse(Path::seekForFiles('install')) as $installFile) {

            $origin = $this->getOrigin($installFile);

            if ($origin != 'current-project') {
                Log::add('mx', "Install [$origin]", function () use ($installFile, $origin) {
                    ob_start();
                    $script = require $installFile;
                    ob_end_clean();

                    if (is_trait($script, TerminalInstallTrait::class)) {
                        Terminal::echoln("Installing [#c:p,$origin]");
                        $script();
                        Terminal::echoln();
                    }
                });
            }
        }

        Terminal::run('composer 1');
    }

    protected function getOrigin($path)
    {
        if ($path === 'install') return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }
};
