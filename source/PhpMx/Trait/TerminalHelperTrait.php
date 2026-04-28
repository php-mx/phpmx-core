<?php

namespace PhpMx\Trait;

use Closure;
use PhpMx\Path;
use PhpMx\Terminal;

/** @ignore */
trait TerminalHelperTrait
{
    protected array $key = [];

    /**
     * Escaneia itens em todos os paths registrados para um diretório, exibindo-os agrupados por origem.
     * Itens que existem em mais de uma origem são marcados como substituídos (replaced).
     * @param string $scan Caminho relativo do diretório a escanear nos paths registrados.
     * @param string|null $filter Prefixo para filtrar os itens pelo campo 'filter'. Null exibe todos.
     * @param Closure|string|null $echo Template ou Closure para exibir um item normal.
     * @param Closure|string|null $replaced Template ou Closure para exibir um item substituído.
     */
    protected function handle(string $scan, ?string $filter, null|Closure|string $echo = null, null|Closure|string $replaced = null)
    {
        $echo = $echo ?? ' [#c:p,#name]';
        $replaced = $replaced ?? ' [#c:pd,#name] [#c:wd,replaced]';

        $echo = is_closure($echo) ? $echo : fn($item) => Terminal::echol($echo, $item);
        $replaced = is_closure($replaced) ? $replaced : fn($item) => Terminal::echol($replaced, $item);

        $paths = Path::seekForDirs($scan);

        $origins = [];
        foreach ($paths as $path) {
            $items = $this->scan($path);
            foreach ($items as $p => $item) {

                $item['filter'] = $item['filter'] ?? $item['name'];

                if (isset($this->key[$item['_key']])) {
                    $item['replaced'] = $this->key[$item['_key']];
                } else {
                    $item['replaced'] = false;
                    $this->key[$item['_key']] = $item;
                }
                $items[$p] = $item;
            }

            usort($items, fn($a, $b) => $a['name'] <=> $b['name']);
            $origins[Path::origin($path)] = $items;
        }

        $origins = array_reverse($origins);

        $originsLn = -1;
        foreach ($origins as $origin => $items) {

            if (!is_null($filter)) $items = array_filter($items, fn($item) => is_null($filter) || str_starts_with(strtolower($item['filter']), strtolower($filter)));

            if (count($items)) {
                if (++$originsLn) Terminal::echol();
                Terminal::echol('[#c:sb,#]', strtoupper($origin));
                foreach ($items as $item) {
                    Terminal::echol();
                    !$item['replaced'] ? $echo($item) : $replaced($item);
                }
            }
        }

        if ($originsLn == -1) Terminal::echol('[#c:dd,- empty -]');
    }

    protected function scan(string $path)
    {
        return [];
    }
}
