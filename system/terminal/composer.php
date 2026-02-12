<?php

use PhpMx\Dir;
use PhpMx\Json;
use PhpMx\Terminal;

/**
 * Gerencia o mapeamento automático do Composer para o framework.
 * @param int forceDev Define se deve forçar o dump em modo desenvolvimento (0 ou 1).
 */
return new class {

    function __invoke($forceDev = 0)
    {
        Terminal::echol('Updating [#c:s,composer.json]');

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

        foreach ($files as $file)
            if (!in_array($file, $composer['autoload']['files']))
                Terminal::echol("[#c:p,$file]");

        $composer['autoload']['files'] = $files;

        Json::export('composer', $composer, false);


        $forceDev || env('DEV') ? self::inDev() : self::inProd();
    }

    protected static function inDev()
    {
        Terminal::echol('Running [#c:s,composer dump-autoload]');
        echo shell_exec("composer dump-autoload");
    }

    protected static function inProd()
    {
        Terminal::echol('Running [#c:s,composer dump-autoload --no-dev --optimize]');
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
