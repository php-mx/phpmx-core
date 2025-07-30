<?php

use PhpMx\Log;
use PhpMx\Path;
use PhpMx\Terminal;
use PhpMx\TerminalInstall;

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

                    if (is_extend($script, TerminalInstall::class)) {
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
