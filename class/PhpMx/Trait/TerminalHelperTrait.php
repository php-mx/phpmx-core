<?php

namespace PhpMx\Trait;

use Closure;
use PhpMx\Autodoc;
use PhpMx\Path;
use PhpMx\Terminal;

/** Utilitários para criação de helpre via terminal. */
trait TerminalHelperTrait
{
    protected $key = [];

    protected function handle(string $scan, ?string $filter, null|Closure|string $echo = null, null|Closure|string $replaced = null)
    {
        $echo = $echo ?? ' - [#c:p,#ref] [#description]';
        $replaced = $replaced ?? ' - [#c:pd,#ref] [#c:wd,replaced]';

        $echo = is_closure($echo) ? $echo : fn($item) => Terminal::echol($echo, $item);
        $replaced = is_closure($replaced) ? $replaced : fn($item) => Terminal::echol($replaced, $item);

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
                $item['description'] = $item['description'] ?? '';
                $items[$p] = $item;
            }
            usort($items, fn($a, $b) => $a['ref'] <=> $b['ref']);
            $origins[Autodoc::originPath($path, $scan)] = $items;
        }

        $origins = array_reverse($origins);

        $originsLn = -1;
        foreach ($origins as $origin => $items) {

            if (!is_null($filter))
                $items = array_filter($items, fn($item) => is_null($filter) || str_starts_with(strtolower($item['filter']), strtolower($filter)));

            if (count($items)) {
                if (++$originsLn) Terminal::echol();
                Terminal::echol('[#c:sb,#]', $origin);
                foreach ($items as $item) {
                    Terminal::echol();
                    !$item['replaced'] ? $echo($item) : $replaced($item);
                }
            }
        }

        if ($originsLn == -1)
            Terminal::echol('[#c:dd,- empty -]');
    }
}
