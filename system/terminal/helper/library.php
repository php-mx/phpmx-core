<?php

use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;

/** Lista as bibliotecas registradas no projeto */
return new class {

    protected $used = [];

    function __invoke()
    {
        foreach (Path::seekForDirs('library') as $n => $path) {
            $origin = $this->getOrigim($path);

            if ($n > 0) Terminal::echo();

            Terminal::echo('[#greenB:#]', $origin);

            foreach ($this->getFilesIn($path, $origin) as $file) {
                Terminal::echo();
                Terminal::echo('[#cyan:#ref] [#blueD:#file][#yellowD:#status]', $file);
            }
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'library') return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }

    protected function getFilesIn($path, $origin)
    {
        $files = [];
        foreach (Dir::seekForFile($path, true) as $ref) {
            $file = path($path, $ref);
            $this->used[$ref] = $this->used[$ref] ?? $origin;

            if ($this->used[$ref] == $origin) {
                $files[$ref] = [
                    'ref' => $ref,
                    'file' => $file,
                    'status' => ''
                ];
            } else {
                $files[$ref] = [
                    'ref' => $ref,
                    'file' => '',
                    'status' => 'replaced in ' . $this->used[$ref]
                ];
            }
        }
        ksort($files);
        return $files;
    }
};
