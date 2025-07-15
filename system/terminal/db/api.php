<?php

use PhpMx\Datalayer;
use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

    protected string $dbName = '';
    protected string $namespace = '';
    protected string $path = '';
    protected array $map = [];

    function __invoke($dbName, $tables, $namespace)
    {
        self::echo('Installing drivers');

        Terminal::run("db.driver $dbName");

        self::echo('');
        self::echo('Creating controllers');
        self::echoLine();

        $dbName = Datalayer::internalName($dbName);

        $map = Datalayer::get($dbName)->getConfig('__dbMap') ?? [];

        $tables = $tables == '*' ? array_keys($map) : explode(',', $tables);

        $namespace = explode('.', "Controller.$namespace");
        $namespace = array_map(fn($v) => strToPascalCase($v), $namespace);
        $namespace = implode('\\', $namespace);

        $path = path('class', $namespace);

        foreach ($tables as $tableName) {
            $tableName = Datalayer::internalName($tableName);
            if (isset($map[$tableName]))
                $this->map[$tableName] = $map[$tableName];
        }

        $this->dbName = $dbName;
        $this->namespace = $namespace;
        $this->path = $path;

        foreach ($this->map as $tableName => $tableMap) {
            $this->createController($tableName, $tableMap);
            self::echo(" [OK] table $tableName");
        }
    }

    protected function createController(string $tableName, array $tableMap)
    {
        $fileName = strToPascalCase($tableName);

        $ignoredFields = [];
        $createInputFields = [];
        $updateInputFields = [];

        foreach ($tableMap['fields'] as $fieldName => $fieldMap) {
            if ($fieldMap['type'] == 'code' || $fieldMap['type'] == 'hash')
                $ignoredFields[] = $fieldName;

            if (!str_starts_with($fieldName, '_')) {
                $data = [
                    'fieldName' => $fieldName,
                    'fieldRequired' => $fieldMap['null'] || !is_null($fieldMap['default']) ? '->required(false)' : '',
                ];

                $data['fieldInputType'] = match ($fieldMap['type']) {
                    'boolean' => 'fieldBool',
                    default => 'field',
                };

                $createInputFields[] =  $this->template('controller/api/inputField', $data);

                if ($fieldMap['type'] == 'code' || $fieldMap['type'] == 'hash')
                    $data['fieldRequired'] = '->required(false)';

                $updateInputFields[] =  $this->template('controller/api/inputField', $data);
            }
        }

        $ignoredFields = array_map(fn($v) => "'$v'", $ignoredFields);

        $data = [
            'className' => strToPascalCase($tableName),
            'tableName' => strToCamelCase($tableName),
            'ignoredFields' => '[' . implode(', ', $ignoredFields) . ']',
            'createInputfields' => implode("\n", $createInputFields),
            'updateInputFields' => implode("\n", $updateInputFields),
        ];

        $content = $this->template('controller/api/class', $data);

        File::create(path($this->path, "$fileName.php"), $content, true);
    }

    /** Retrona um teplate de driver */
    protected function template(string $file, array $data = []): string
    {
        $template = Path::seekForFile("library/template/terminal/datalayer/$file.txt");

        $data['dbName'] = strToCamelCase($this->dbName);
        $data['namespace'] = $this->namespace;

        $template = Import::content($file, $data);

        return prepare($template, $data);
    }
};
