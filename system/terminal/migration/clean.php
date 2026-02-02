<?php

use PhpMx\Datalayer\MigrationTerminalTrait;

/** Reverte todas as migrações aplicadas no banco de dados até o estado inicial */
return new class {

    use MigrationTerminalTrait;

    function __invoke($dbName = 'main')
    {
        while (self::down($dbName));
    }
};
