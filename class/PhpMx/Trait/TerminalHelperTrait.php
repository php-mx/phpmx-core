<?php

namespace PhpMx\Trait;

use Closure;
use PhpMx\Path;
use PhpMx\Terminal;

/** Utilitários para criação de helpre via terminal. */
trait TerminalHelperTrait
{
    protected $key = [];

    protected function handle(string $scan, ?string $filter, Closure|string $echo = 'ref', Closure|string $replaced = 'ref')
    {
        $replaced = $replaced ?? $echo;
        $echo = is_closure($echo) ? $echo : fn($item) => Terminal::echoln(" - [#c:p,#$echo]", $item);
        $replaced = is_closure($replaced) ? $replaced : fn($item) => Terminal::echoln(" - [#c:pd,#$replaced] [#c:wd,replaced]", $item);

        $originsLn = -1;
        $paths = Path::seekForDirs($scan);

        $origins = [];
        foreach ($paths as $path) {
            $items = $this->scan($path);
            foreach ($items as $p => $item) {
                $item['filter'] = $item['filter'] ?? $item['ref'];
                if (isset($this->key[$item['ref']])) {
                    $item['replaced'] = $this->key[$item['ref']];
                } else {
                    $item['replaced'] = false;
                    $this->key[$item['ref']] = $item;
                }
                $items[$p] = $item;
            }
            usort($items, fn($a, $b) => $a['ref'] <=> $b['ref']);
            $origins[$this->origin($path, $scan)] = $items;
        }

        $origins = array_reverse($origins);

        foreach ($origins as $origin => $items) {

            if (!is_null($filter))
                $items = array_filter($items, fn($item) => is_null($filter) || str_starts_with(strtolower($item['filter']), strtolower($filter)));

            if (count($items)) {
                if (++$originsLn) Terminal::echoln();
                Terminal::echoln('[#c:sb,#]', $origin);
                foreach ($items as $item) {
                    !$item['replaced'] ? $echo($item) : $replaced($item);
                }
            }
        }
    }

    protected function origin($path, $base)
    {
        if ($path === $base) return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }
}
