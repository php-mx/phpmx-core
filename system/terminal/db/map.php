<?php

use PhpMx\Datalayer;
use PhpMx\Json;
use PhpMx\Terminal;

/** Exporta o mapeamento interno do banco para um arquivo JSON */
return new class {

    function __invoke($dbName = 'main')
    {
        $dbName = Datalayer::internalName($dbName);

        $map = Datalayer::get($dbName)->getConfig('__dbmap') ?? [];

        $file = path("system/datalayer/$dbName/scheme/map.json");

        Json::export($file, $map);

        Terminal::echo("[#green:$dbName] map exported to [#whiteD:$file]");
    }
};
