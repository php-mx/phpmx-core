<?php

use PhpMx\Cif;
use PhpMx\File;
use PhpMx\Terminal;

return new class {

    function __invoke($cifName)
    {
        $file = path("library/certificate/$cifName");
        $file = File::setEx($file, 'crt');

        if (File::check($file))
            throw new Exception("Cif file [$cifName] already exists");

        $allowChar = Cif::BASE;

        $content = [];
        while (count($content) < 63) {
            $charKey = str_shuffle($allowChar);

            while ($charKey == $allowChar || in_array($charKey, $content))
                $charKey = str_shuffle($allowChar);

            $charKey = implode(' ', str_split($charKey, 2));
            $content[] = $charKey;
        }

        $content = implode(' ', $content);

        $content = str_split($content, 21);

        $content = array_map(fn($value) => trim($value), $content);

        $content = implode("\n", $content);

        File::create($file, $content, true);

        Terminal::echo('Certificate [[#].crt] created successfully.', $cifName);
        Terminal::echo('To use the new file in your project, add the line below to your environment variables');
        Terminal::echo('');
        Terminal::echo('CIF = [#]', $cifName);
    }
};
