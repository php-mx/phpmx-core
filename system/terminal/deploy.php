<?php

use PhpMx\Log;
use PhpMx\Path;
use PhpMx\Terminal;

/** Executa os scripts de deploy de todos os pacotes mx registrados. */
return new class {

    function __invoke()
    {
        if (getenv('MX_DEPLOYING')) return;

        putenv('MX_DEPLOYING=1');

        foreach (array_reverse(Path::seekForFiles('deploy')) as $deployFile) {

            $origin = Path::origin($deployFile);

            Log::add('mx', "Deploy [$origin]", function () use ($deployFile, $origin) {
                ob_start();
                $script = require $deployFile;
                ob_end_clean();

                if (is_object($script) && is_callable($script)) {
                    Terminal::echol("Deploying [#c:p,$origin]");
                    $script();
                    Terminal::echol();
                }
            });
        }

        putenv('MX_DEPLOYING');
    }
};
