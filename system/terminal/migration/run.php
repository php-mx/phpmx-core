<?php

use PhpMx\Datalayer\MigrationTerminalTrait;

/** Executa todas as migrações pendentes para atualizar o esquema do banco de dados até a versão mais recente */
return new class {

    use MigrationTerminalTrait;

    function __invoke($dbName = 'main')
    {
        while (self::up($dbName));
    }
};
