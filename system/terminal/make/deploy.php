<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

/** Cria o arquivo de script "deploy" na raiz do projeto para automatizar rotinas de deploy. */
return new class {

    function __invoke()
    {
        if (File::check('deploy'))
            throw new Exception("Deploy already exists in project");

        $template = Path::seekForFile('storage/template/terminal/deploy.txt');
        $template = Import::content($template);

        File::create('deploy', $template);

        Terminal::echol("File [#c:p,deploy] created successfully");
    }
};
