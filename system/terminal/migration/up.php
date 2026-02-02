<?php

use PhpMx\Datalayer\MigrationTerminalTrait;

/** Executa a próxima migração pendente no banco de dados para avançar uma versão */
return new class {

    use MigrationTerminalTrait;

    function __invoke($dbName = 'main')
    {
        self::up($dbName);
    }
};
