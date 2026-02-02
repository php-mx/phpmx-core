<?php

use PhpMx\Dir;
use PhpMx\Json;
use PhpMx\Terminal;

/** Atualiza o autoload do composer mapeando automaticamente as classes e arquivos de helper do sistema */
return new class {

    function __invoke($forceDev = 0)
    {
        $composer = Json::import('composer');

        $composer['autoload'] = $composer['autoload'] ?? [];
        $composer['autoload']['psr-4'] = $composer['autoload']['psr-4'] ?? [];
        $composer['autoload']['files'] = $composer['autoload']['files'] ?? [];

        $composer['autoload']['psr-4'][''] = path('class/');

        $autoImport = path('system/helper/');

        $files = [];

        foreach ($composer['autoload']['files'] as $file)
            if (substr($file, 0, strlen($autoImport)) != $autoImport)
                $files[] = $file;

        $files = [...$files, ...self::seekForFile($autoImport)];

        $composer['autoload']['files'] = $files;

        Json::export('composer', $composer, false);

        Terminal::echo('File [#blue:composer.json] updated');

        $forceDev || env('DEV') ? self::inDev() : self::inProd();
    }

    protected static function inDev()
    {
        Terminal::echo('run [#green:composer dump-autoload]');
        echo shell_exec("composer dump-autoload");
    }

    protected static function inProd()
    {
        Terminal::echo('run [#green:composer dump-autoload --no-dev --optimize]');
        echo shell_exec("composer dump-autoload --no-dev --optimize");
    }

    protected static function seekForFile($ref)
    {
        $return = [];

        foreach (Dir::seekForDir($ref) as $dir)
            foreach (self::seekForFile("$ref/$dir") as $file)
                $return[] = path($file);

        foreach (Dir::seekForFile($ref) as $file)
            $return[] = path("$ref/$file");

        return $return;
    }
};
