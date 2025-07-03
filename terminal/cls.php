<?php

use PhpMx\Dir;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke()
    {
        $paths = Dir::seekForDir('.');

        $paths = array_filter($paths, fn($v) => !str_starts_with($v, '.') && $v != 'vendor');

        foreach ($paths as $path) {
            $this->cls($path);
            Dir::remove($path);
        }
    }

    function cls($path)
    {
        foreach (Dir::seekForDir($path) as $dir)
            $this->cls(path($path, $dir));

        Dir::remove($path);
    }
};
