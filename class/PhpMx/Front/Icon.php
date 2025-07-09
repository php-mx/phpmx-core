<?php

namespace PhpMx\Front;

use PhpMx\Dir;
use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;

abstract class Icon
{
    protected static array $cache = [];
    protected static ?array $scheme = null;

    static function get(string $iconRef, ...$styleClass): string
    {
        $svg = self::svg($iconRef);
        $class = implode(' ', $styleClass);
        return "<span class='icon $class'>$svg</span>";
    }

    static function svg(string $iconRef): string
    {
        $file = self::getFile($iconRef) ?? self::getFile('none');

        $hash = md5($file);

        self::$cache[$hash] = self::$cache[$hash] ?? Import::content($file);

        return self::$cache[$hash];
    }

    static function getFile($iconRef): ?string
    {
        $iconRef = strToCamelCase($iconRef);

        self::$scheme = self::$scheme ?? self::$scheme ?? cache('front-icons', function () {

            $icons = [];

            foreach (Path::seekForDirs('library/icons') as $path) {
                $files = Dir::seekForFile($path, true);
                foreach ($files as $file) {
                    $iconRef = File::getName($file);
                    $iconRef = strToCamelCase($iconRef);
                    $icons[$iconRef] = path($path, $file);
                }
            }

            return $icons;
        });


        $file = self::$scheme[$iconRef] ?? null;

        return $file;
    }
}
