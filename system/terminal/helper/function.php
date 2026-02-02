<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

/** Lista todas as funções de helper registradas no sistema */
return new class {

    protected $used = [];

    function __invoke($filter = null)
    {
        foreach (Path::seekForDirs('system/helper/function') as $n => $path) {
            $origin = $this->getOrigim($path);

            $functions = $this->extractFunctionsFromPath($path, $origin);

            $visibleFunctions = array_filter($functions, function ($func) use ($filter) {
                return is_null($filter) || str_starts_with($func['ref'], $filter);
            });

            if (empty($visibleFunctions)) continue;

            if ($n > 0) Terminal::echo();

            Terminal::echo('[#greenB:#]', $origin);

            foreach ($visibleFunctions as $func) {
                Terminal::echo();
                Terminal::echo(' [#cyan:#ref] [#description]', $func);
                Terminal::echo('  [#blueD:#file] [#yellowD:#replaced]', $func);
            }
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/helper/function') return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return ($parts[1] ?? 'unknown') . '-' . ($parts[2] ?? 'unknown');
        }

        return 'unknown';
    }

    protected function extractFunctionsFromPath($path, $origin): array
    {
        $functions = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $file = path($path, $item);
            $content = Import::content($file);

            preg_match_all('/function\s+(\w+)/i', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

            foreach ($matches as $match) {
                $funcName = $match[1][0];
                $pos = $match[0][1];

                $description = $this->getDocBefore($content, $pos);

                $this->used[$funcName] = $this->used[$funcName] ?? $file;

                $functions[$funcName] = [
                    'ref' => $funcName,
                    'description' => $description,
                    'file' => $file,
                    'replaced' => $this->used[$funcName] == $file ? '' : $this->used[$funcName]
                ];
            }
        }
        ksort($functions);
        return $functions;
    }

    protected function getDocBefore(string $code, int $pos): string
    {
        $before = substr($code, 0, $pos);
        if (preg_match_all('/\/\*\*\s*(.*?)\s*\*\//s', $before, $docs)) {
            $lastDoc = end($docs[0]);
            $lastDesc = end($docs[1]);
            $lastPos = strrpos($before, $lastDoc) + strlen($lastDoc);

            if (preg_match('/^[\s\w\$\=]*$/', substr($before, $lastPos))) {
                return trim($lastDesc);
            }
        }
        return '';
    }
};
