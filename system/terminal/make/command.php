<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke(string $command)
    {
        $command = remove_accents($command);
        $command = strtolower($command);

        $file = explode('.', $command);
        $file = array_map(fn($v) => strtolower($v), $file);
        $file = path('system/terminal', ...$file);
        $file = File::setEx($file, 'php');

        if (File::check($file))
            throw new Exception("Command [$command] already exists in project");

        $template = Path::seekForFile('library/template/terminal/command.txt');
        $template = Import::content($template, ['command' => $command]);

        File::create($file, $template);

        self::echo('Command [[#]] created successfully', $command);
        self::echo('[[#]]', $file);
    }
};
