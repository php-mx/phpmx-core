<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

/**
 * Cria um novo arquivo de comando para o terminal em: system/terminal/{command}.php
 * @param string command Nome do comando a ser criado.
 */
return new class {

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

        Terminal::echol("File [#c:p,#] created successfully", $file);
        Terminal::echol();
        Terminal::echol("   [#c:s,php mx] [#c:s,#]", [$command]);
    }
};
