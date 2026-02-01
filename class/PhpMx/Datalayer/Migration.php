<?php

namespace PhpMx\Datalayer;

use PhpMx\Datalayer;
use PhpMx\Datalayer\Scheme\Scheme;
use PhpMx\Datalayer\Scheme\SchemeField;
use PhpMx\Datalayer\Scheme\SchemeTable;

/** Classe base para definir migrations de banco com suporte a tipagem e aplicação via Scheme. */
abstract class Migration
{
    protected Scheme $scheme;
    protected string $dbName;
    protected $lock = false;

    final function execute(string $dbName, bool $mode)
    {
        $this->dbName = Datalayer::internalName($dbName);

        $this->scheme = new Scheme($this->dbName);

        $mode ? $this->up() : $this->down();

        $this->scheme->apply();
    }

    abstract function up();

    abstract function down();

    /** Retorna o objeto de uma tabela */
    function &table(string $table, ?string $comment = null): SchemeTable
    {
        $returnTable = $this->scheme->table($table, $comment)->fields([
            $this->fTimestamp('=_created', 'moment of record creation')->default(true)->index(true),
            $this->fTimestamp('=_updated', 'moment of last record update')->default(null)->index(true),
            $this->fTimestamp('=_deleted', 'moment of record deletion')->default(null)->index(true),
        ]);
        return $returnTable;
    }

    /** Campo TINYINT */
    function fTinyint(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'tinyint', 'comment' => $comment]);
    }

    /** Campo SMALLINT */
    function fSmallint(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'smallint', 'comment' => $comment]);
    }

    /** Campo MEDIUMINT */
    function fMediumint(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'mediumint', 'comment' => $comment]);
    }

    /** Campo INT */
    function fInt(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'int', 'comment' => $comment]);
    }

    /** Campo BIGINT */
    function fBigint(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'bigint', 'comment' => $comment]);
    }

    /** Campo DECIMAL */
    function fDecimal(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'decimal', 'comment' => $comment]);
    }

    /** Campo FLOAT */
    function fFloat(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'float', 'comment' => $comment]);
    }

    /** Campo DOUBLE */
    function fDouble(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'double', 'comment' => $comment]);
    }

    /** Campo BOOLEAN */
    function fBoolean(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'boolean', 'comment' => $comment]);
    }

    /** Campo CHAR */
    function fChar(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'char', 'comment' => $comment]);
    }

    /** Campo VARCHAR */
    function fVarchar(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'varchar', 'comment' => $comment]);
    }

    /** Campo TEXT */
    function fText(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'text', 'comment' => $comment]);
    }

    /** Campo DATE */
    function fDate(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'date', 'comment' => $comment]);
    }

    /** Campo TIME */
    function fTime(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'time', 'comment' => $comment]);
    }

    /** Campo DATETIME */
    function fDatetime(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'datetime', 'comment' => $comment]);
    }

    /** Campo TIMESTAMP */
    function fTimestamp(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'timestamp', 'comment' => $comment]);
    }

    /** Campo JSON */
    function fJson(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'json', 'comment' => $comment]);
    }

    /** Campo BLOB */
    function fBlob(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'blob', 'comment' => $comment]);
    }

    /** Campo índice de referência (foreign key) */
    function fIdx(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, [
            'type'    => 'idx',
            'comment' => $comment,
            'index'   => true,
            'settings' => [
                'datalayer' => $this->dbName,
                'table'     => $name,
            ],
        ]);
    }

    /** Campo Email */
    function fEmail(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'email', 'comment' => $comment]);
    }

    /** Campo hash MD5 */
    function fMd5(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'md5', 'comment' => $comment]);
    }

    /** Campo Password */
    function fPassword(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'password', 'comment' => $comment]);
    }
}
