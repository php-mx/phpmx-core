<?php

use PhpMx\File;
use PhpMx\Terminal;

/**
 * Gera um novo arquivo de exemplo em storage/example.
 * @param string $fileName Nome do arquivo.
 */
return new class {

    function __invoke($fileName)
    {
        $fileName = strToCamelCase($fileName);

        $file = path('storage/example', "$fileName.php");

        if (File::check($file))
            throw new Exception("File [$file] already exists");

        File::create($file, "<?php\n");

        Terminal::echol("File [#c:p,#] created successfully", $file);
    }
};
