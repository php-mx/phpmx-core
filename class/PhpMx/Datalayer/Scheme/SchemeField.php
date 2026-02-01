<?php

namespace PhpMx\Datalayer\Scheme;

use Exception;
use PhpMx\Datalayer;

/** Representa um campo da tabela no esquema de banco de dados, permitindo configuração detalhada. */
class SchemeField
{
    protected $name;
    protected $map;

    protected $isDroped = false;

    function __construct(string $name, array $map = [], ?array $realMap = null)
    {
        $name = str_starts_with($name, '=') ? substr($name, 1) : Datalayer::internalName($name);

        $realMap = $realMap ?? SchemeMap::FIELD_MAP;

        $this->name = $name;

        $this->map['type'] = $map['type'] ?? $realMap['type'];
        $this->map['size'] = $map['size'] ?? $realMap['size'];
        $this->map['null'] = $map['null'] ?? $realMap['null'];
        $this->map['index'] = $map['index'] ?? $realMap['index'];
        $this->map['unique'] = $map['unique'] ?? $realMap['unique'];
        $this->map['comment'] = $map['comment'] ?? $realMap['comment'];
        $this->map['default'] = $map['default'] ?? $realMap['default'];
        $this->map['settings'] = $map['settings'] ?? $realMap['settings'];
    }

    protected function onlyType(string $name, array $allowTypes = [], array $denyType = []): void
    {
        if ((!empty($allowTypes) && !in_array($this->map['type'], $allowTypes)) || in_array($this->map['type'], $denyType))
            throw new Exception(prepare("Unsupported [$name] to fields [[#]]", $this->map['type']));
    }

    /** Marca/Desmarca o campo para a remoção */
    function drop(bool $drop = true): static
    {
        $this->isDroped = boolval($drop);
        return $this;
    }

    /** Define o comentário do campo */
    function comment(string $comment): static
    {
        $this->map['comment'] = $comment;
        return $this;
    }

    /** Define o valor padrão do campo */
    function default(mixed $default): static
    {
        $this->onlyType('default', denyType: ['password', 'text', 'blob', 'json']);
        $this->map['default'] = $default;
        if (is_null($default)) $this->null(true);
        return $this;
    }

    /** Define se o campo aceita valores nulos */
    function null(bool $null): static
    {
        $this->map['null'] = boolval($null);
        return $this;
    }

    /** Define se o campo deve ser indexado */
    function index(bool $index, bool $unique = false): static
    {
        $this->onlyType('index', denyType: ['text', 'blob']);
        $this->map['index'] = $index;
        $this->map['unique'] = $index && $unique;
        return $this;
    }

    /** Define se o campo deve ser único */
    function unique(bool $unique): static
    {
        $this->onlyType('unique', denyType: ['text', 'blob']);
        $this->map['unique'] = boolval($unique);
        if ($unique) $this->map['index'] = true;
        return $this;
    }

    /** Define o tamanho máximo do campo */
    function size(int $size): static
    {
        $this->onlyType('size', ['char', 'varchar']);
        $this->map['size'] = max(0, intval($size));
        return $this;
    }

    /** Determina o valor mínimo do campo */
    function min(int|float $min): static
    {
        $this->onlyType('min', ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double']);
        $this->map['settings']['min'] = $min;
        return $this;
    }

    /** Determina o valor máximo do campo */
    function max(int|float $max): static
    {
        $this->onlyType('max', ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double']);
        $this->map['settings']['max'] = $max;
        return $this;
    }

    /** Determina a forma de arredondamento do campo [-1:baixo, 0:automático, 1:cima] */
    function round(int $round): static
    {
        $this->onlyType('round', ['tinyint', 'smallint', 'mediumint', 'int', 'bigint']);
        $this->map['settings']['round'] = max(-1, min(1, intval($round)));
        return $this;
    }

    /** Determina se o campo deve cortar conteúdo com mais caracteres que o permitido */
    function crop(bool $crop): static
    {
        $this->onlyType('crop', ['char', 'varchar']);
        $this->map['settings']['crop'] = boolval($crop);
        return $this;
    }

    /** Determina a conexão referenciada pelo campo */
    function datalayer(string $datalayer): static
    {
        $this->onlyType('datalayer', ['idx']);
        $this->map['settings']['datalayer'] = $datalayer;
        return $this;
    }

    /** Determina a tabela referenciada pelo campo */
    function table(string $table): static
    {
        $this->onlyType('table', ['idx']);
        $this->map['settings']['table'] = $table;
        return $this;
    }

    /** Retorna o nome do campo */
    function getName(): string
    {
        return $this->name;
    }

    /** Retorna o mapa do campo */
    function getMap(): bool|array
    {
        if ($this->isDroped) return false;

        return match ($this->map['type']) {
            'tinyint', 'smallint', 'mediumint', 'int', 'bigint' => $this->__mapInteger($this->map),
            'decimal', 'float', 'double' => $this->__mapNumeric($this->map),
            'boolean' => $this->__mapBoolean($this->map),
            'char' => $this->__mapChar($this->map),
            'varchar' => $this->__mapVarchar($this->map),
            'text', 'blob' => $this->__mapText($this->map),
            'date', 'time', 'datetime', 'timestamp' => $this->__mapTemporal($this->map),
            'json' => $this->__mapJson($this->map),
            'email' => $this->__mapEmail($this->map),
            'md5' => $this->__mapMd5($this->map),
            'password' => $this->__mapPassword($this->map),
            'idx' => $this->__mapIdx($this->map),
            default => throw new Exception("Invalid field type [{$this->map['type']}] in [{$this->name}]")
        };
    }

    protected function __mapBoolean(array $map): array
    {
        $map['size'] = 1;

        if (!is_null($map['default']))
            $map['default'] = intval(boolval($map['default']));

        return $map;
    }

    private function __mapInteger(array $map): array
    {
        if (!is_null($map['default']))
            $map['default'] = intval($map['default']);

        return $map;
    }

    private function __mapNumeric(array $map): array
    {
        if (!is_null($map['default']))
            $map['default'] = floatval($map['default']);

        // $map['settings']['decimal'] = $map['settings']['decimal'] ?? 2;

        return $map;
    }

    protected function __mapChar(array $map): array
    {
        $map['size'] = $map['size'] ?? 1;

        if (!is_null($map['default']))
            $map['default'] = substr(strval($map['default']), 0, $map['size']);

        return $map;
    }

    protected function __mapVarchar(array $map): array
    {
        $map['size'] = $map['size'] ?? 255;

        if (!is_null($map['default']))
            $map['default'] = substr(strval($map['default']), 0, $map['size']);

        return $map;
    }

    protected function __mapText(array $map): array
    {
        $map['size'] = null;

        if (!is_null($map['default']))
            $map['default'] = strval($map['default']);

        return $map;
    }

    private function __mapTemporal(array $map): array
    {
        $map['size'] = null;

        if (is_string($map['default'])) {
            $value = strval($map['default']);
            $valid = match ($map['type']) {
                'date' => date_create_from_format('Y-m-d', $value) !== false,
                'time' => date_create_from_format('H:i:s', $value) !== false,
                'datetime' => date_create_from_format('Y-m-d H:i:s', $value) !== false,
                'timestamp' => date_create_from_format('Y-m-d H:i:s', $value) !== false,
            };

            if (!$valid)
                throw new Exception("Invalid default value for [{$map['type']}] field [{$this->name}]");

            $map['default'] = $value;
        }

        if (is_bool($map['default'])) {

            if ($map['default'] && !in_array($map['type'], ['datetime', 'timestamp']))
                throw new Exception("CURRENT_TIMESTAMP is only supported for datetime and timestamp fields [{$this->name}]");

            $map['default'] = $map['default'] ? 'CURRENT_TIMESTAMP' : null;
        }

        return $map;
    }

    protected function __mapJson(array $map): array
    {
        $map['size'] = null;

        return $map;
    }

    protected function __mapIdx(array $map): array
    {
        $map['size'] = 10;
        $map['index'] = true;
        $map['settings']['datalayer'] = Datalayer::internalName($map['settings']['datalayer']);
        $map['settings']['table'] = Datalayer::internalName($map['settings']['table']);

        return $map;
    }

    protected function __mapEmail(array $map): array
    {
        $map['size'] = 254;

        if (!is_null($map['default'])) {
            $map['default'] = strtolower(filter_var(strval($map['default']), FILTER_SANITIZE_EMAIL));

            if (!filter_var($map['default'], FILTER_VALIDATE_EMAIL))
                throw new Exception("Invalid email default value in [{$this->name}]");
        }

        return $map;
    }

    protected function __mapMd5(array $map): array
    {
        $map['size'] = 32;

        if (!is_null($map['default'])) {
            if (!is_md5($map['default']))
                $map['default'] = md5(strval($map['default']));

            $map['default'] = strtolower($map['default']);
        }

        return $map;
    }

    protected function __mapPassword(array $map): array
    {
        $map['size'] = 255;
        $map['null'] = false;
        $map['default'] = null;

        return $map;
    }
}
