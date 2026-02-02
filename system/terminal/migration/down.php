<?php

use PhpMx\Datalayer\MigrationTerminalTrait;

/** Reverte a última migração aplicada no banco de dados para o estado anterior */
return new class {

    use MigrationTerminalTrait;

    function __invoke($dbName = 'main')
    {
        self::down($dbName);
    }
};
