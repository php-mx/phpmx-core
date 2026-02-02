<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

/** Lista todas as constantes helper registradas no sistema */
return new class {

    protected $used = [];

    function __invoke($filter = null)
    {
        foreach (Path::seekForDirs('system/helper/constant') as $nPath => $path) {
            $origin = $this->getOrigim($path);

            $constants = $this->getConstantsIn($path, $origin);

            // Filtra as constantes antes de exibir a origem
            $visibleConstants = array_filter($constants, function ($const) use ($filter) {
                return is_null($filter) || str_starts_with($const['ref'], $filter);
            });

            if (empty($visibleConstants)) continue;

            if ($nPath > 0) Terminal::echo();

            Terminal::echo('[#greenB:#]', $origin);

            foreach ($visibleConstants as $const) {
                Terminal::echo();
                Terminal::echo('[#cyan:#ref] [#whiteD:#description][#yellowD:#status]', $const);
            }
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/helper/constant') return 'current-project';
        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return ($parts[1] ?? 'unknown') . '-' . ($parts[2] ?? 'unknown');
        }
        return 'unknown';
    }

    protected function getConstantsIn($path, $origin)
    {
        $constants = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $file = path($path, $item);
            $content = Import::content($file);

            preg_match_all('/(?:\/\*\*\s*(.*?)\s*\*\/\s*\n\s*)?define\(\s*[\'"](\w+)[\'"]/s', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $ref = $match[2];
                $this->used[$ref] = $this->used[$ref] ?? $origin;

                $constants[$ref] = [
                    'ref' => $ref,
                    'description' => trim($match[1] ?? ''),
                    'file' => ($this->used[$ref] == $origin) ? $file : '',
                    'status' => ($this->used[$ref] == $origin) ? '' : 'replaced in ' . $this->used[$ref]
                ];
            }
        }
        ksort($constants);
        return $constants;
    }
};
