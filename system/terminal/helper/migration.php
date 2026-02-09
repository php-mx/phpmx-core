<?php

use PhpMx\Datalayer;
use PhpMx\File;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;
use PhpMx\Trait\TerminalMigrationTrait;

/** */
return new class {
    use TerminalMigrationTrait;

    function __invoke($dbName = 'main')
    {
        self::loadDatalayer($dbName);
        $files = self::getFiles();

        if (!empty($files)) {
            $executeds = Datalayer::get($dbName)->getConfigGroup('migration');

            Terminal::echol('[#c:sb,#]', Datalayer::externalName($dbName));

            foreach ($files as $id => $file) {
                $name = substr(File::getName($file), strlen($id) + 1);

                $executed = isset($executeds[$id]);
                $locked = boolval($executeds[$id]['lock'] ?? false) ? "[locked]" : '';

                $color = $executed ? 's' : 'd';
                if ($locked) $color .= 'd';

                Terminal::echo(" - [#c:$color,$name] [#c:sd,$file]");
                if ($locked)
                    Terminal::echo(" [#c:wd,#]", $locked);
                Terminal::echol();
            }
        } else {
            Terminal::echol('[#c:dd,- empty -]');
        }
    }
};
