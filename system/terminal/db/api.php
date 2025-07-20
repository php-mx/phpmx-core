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

        $content = $this->template('class/class', [
            'key' => strToCamelCase("$tableName key"),
            'className' => $className,
            'table' => strToCamelCase($tableName),
            'model' => $model,
            'useModel' => "\\Model\\$model\\$model",
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
