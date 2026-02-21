<?php

use PhpMx\Log;
use PhpMx\Path;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalInstallTrait;

/** Executa os scripts de instalação de pacotes mx externos. */
return new class {

    function __invoke()
    {
        foreach (array_reverse(Path::seekForFiles('install')) as $installFile) {

            $origin = Path::origin($installFile);

            if ($origin != 'current-project') {
                Log::add('mx', "Install [$origin]", function () use ($installFile, $origin) {
                    ob_start();
                    $script = require $installFile;
                    ob_end_clean();

                    if (is_trait($script, TerminalInstallTrait::class)) {
                        Terminal::echol("Installing [#c:p,$origin]");
                        $script();
                        Terminal::echol();
                    }
                });
            }
        }

        Terminal::run('composer 1');
    }
};
