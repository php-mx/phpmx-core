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

    function __invoke($dbName)
    {
        self::echo('Creating api');
        self::echoLine();

        $dbName = Datalayer::internalName($dbName);
        $map = Datalayer::get($dbName)->getConfig('__dbmap') ?? [];
        $namespace = 'Controller\\Api\\' . strToPascalCase($dbName);
        $path = path('class', $namespace);

        $this->dbName = $dbName;
        $this->map = $map;
        $this->namespace = $namespace;
        $this->path = $path;

        foreach ($this->map as $tableName => $tableMap)
            $this->createClass($tableName, $tableMap);

        $this->createRoute();

        self::echoLine();
        self::echo('Api created');
    }

    protected function createClass($tableName, $tableMap)
    {
        $className = strToPascalCase($tableName);
        $model = strToPascalCase('db ' . $this->dbName);

        $scheme = [];
        $inputCreate = [];
        $inputUpdate = [];

        foreach ($tableMap['fields'] as $fieldName => $fieldMap) {
            if ($fieldMap['type'] != 'md5' && $fieldMap['type'] != 'mx5') {
                if ($fieldMap['type'] == 'idx') {
                    $scheme[] = "            '{$fieldName}Key' => fn(\$record) => \$record->{$fieldName}->idkey()";
                } else {
                    $scheme[] = "            '$fieldName'";
                }
            }

            if (!str_starts_with($fieldName, '_')) {
                $required = $fieldMap['null'] || !is_null($fieldMap['default']) ? '->required(false)' : '';

                $create = "        ";
                $update = "        ";

                switch ($fieldMap['type']) {
                    case 'boolean':
                        $create .= "\$input->fieldBool('$fieldName'){$required};";
                        $update .= "\$input->fieldBool('$fieldName')->required(false);";
                        break;
                    case 'email':
                        $create .= "\$input->field('$fieldName'){$required}->validate(FILTER_VALIDATE_EMAIL)->sanitize(FILTER_SANITIZE_EMAIL);";
                        $update .= "\$input->field('$fieldName')->required(false)->validate(FILTER_VALIDATE_EMAIL)->sanitize(FILTER_SANITIZE_EMAIL);";
                        break;
                    case 'float':
                        $create .= "\$input->field('$fieldName'){$required}->validate(FILTER_VALIDATE_FLOAT)->sanitize(FILTER_SANITIZE_NUMBER_FLOAT);";
                        $update .= "\$input->field('$fieldName')->required(false)->validate(FILTER_VALIDATE_FLOAT)->sanitize(FILTER_SANITIZE_NUMBER_FLOAT);";
                        break;
                    case 'idx':
                        $validate = prepare('fn($v) => \Model\[#model]\[#model]::$[#table]->getOneKey($v)->_checkInDb()', [
                            'model' => strToPascalCase("db " . $fieldMap['settings']['datalayer']),
                            'table' => strToCamelCase($fieldMap['settings']['table'])
                        ]);

                        $create .= "\$input->field('$fieldName'){$required}->validate($validate)->sanitize(fn(\$v) => idKeyId(\$v));";
                        $update .= "\$input->field('$fieldName')->required(false)->validate($validate)->sanitize(fn(\$v) => idKeyId(\$v));";
                        break;
                    case 'int':
                        $create .= "\$input->field('$fieldName'){$required}->validate(FILTER_VALIDATE_INT)->sanitize(FILTER_SANITIZE_NUMBER_INT);";
                        $update .= "\$input->field('$fieldName')->required(false)->validate(FILTER_VALIDATE_INT)->sanitize(FILTER_SANITIZE_NUMBER_INT);";
                        break;
                    case 'json':
                        $create .= "\$input->fieldScheme('$fieldName'){$required};";
                        $update .= "\$input->fieldScheme('$fieldName')->required(false);";
                        break;
                    case 'md5':
                    case 'mx5':
                    case 'string':
                    case 'text':
                        $create .= "\$input->field('$fieldName'){$required};";
                        $update .= "\$input->field('$fieldName')->required(false);";
                        break;
                    case 'time':
                        $create .= "\$input->field('$fieldName'){$required}->sanitize(fn(\$v) => is_numeric(\$v) ? \$v : strtotime(\$v));";
                        $update .= "\$input->field('$fieldName')->required(false)->sanitize(fn(\$v) => is_numeric(\$v) ? \$v : strtotime(\$v));";
                        break;
                }

                if (!empty(trim($create)))
                    $inputCreate[] = $create;
                if (!empty(trim($update)))
                    $inputUpdate[] = $update;
            }
        }

        $content = $this->template('class/class', [
            'key' => strToCamelCase("$tableName key"),
            'className' => $className,
            'table' => strToCamelCase($tableName),
            'model' => $model,
            'useModel' => "\\Model\\$model\\$model",
            'scheme' => implode(",\n", $scheme),
            'inputCreate' => implode("\n", $inputCreate),
            'inputUpdate' => implode("\n", $inputUpdate),
        ]);

        $path = path($this->namespace);

        File::create("class/$path/$className.php", $content);
    }

    protected function createRoute()
    {
        $content = '';
        foreach (array_keys($this->map) as $table)
            $content .= $this->template('router/route', [
                'route' => strToSnakeCase($table),
                'controller' => strToPascalCase($table),
                'key' => strToCamelCase("$table key")
            ]);

        $content = $this->template('router/group', [
            'routes' => $content,
        ]);

        $fileName = strToCamelCase("db " . $this->dbName);

        File::create("/system/routes/api/$fileName.php", $content);
    }

    protected function template(string $file, array $data = []): string
    {
        $template = Path::seekForFile("library/template/terminal/db/api/$file.txt");

        $data['dbName'] = $this->dbName;
        $data['namespace'] = $this->namespace;

        $template = Import::content($template, $data);

        return prepare($template, $data);
    }
};
