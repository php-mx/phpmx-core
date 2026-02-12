<?php

namespace PhpMx\Trait;

use Closure;
use PhpMx\Path;
use PhpMx\Terminal;

/** @ignore */
trait TerminalHelperTrait
{
    protected $key = [];

    protected function handle(string $scan, ?string $filter, null|Closure|string $echo = null, null|Closure|string $replaced = null)
    {
        $echo = $echo ?? ' - [#c:p,#name] [#description]';
        $replaced = $replaced ?? ' - [#c:pd,#name] [#c:wd,replaced]';

        $echo = is_closure($echo) ? $echo : fn($item) => Terminal::echol($echo, $item);
        $replaced = is_closure($replaced) ? $replaced : fn($item) => Terminal::echol($replaced, $item);

        $paths = Path::seekForDirs($scan);

        $origins = [];
        foreach ($paths as $path) {
            $items = $this->scan($path);
            foreach ($items as $p => $item) {

                $item['filter'] = $item['filter'] ?? $item['name'];
                if (isset($this->key[$item['key']])) {
                    $item['replaced'] = $this->key[$item['key']];
                } else {
                    $item['replaced'] = false;
                    $this->key[$item['key']] = $item;
                }
                $item['description'] = $item['description'] ?? '';
                $items[$p] = $item;
            }
            usort($items, fn($a, $b) => $a['name'] <=> $b['name']);
            $origins[Path::origin($path, $scan)] = $items;
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

    protected static function docBlockBefore(string $code, int $pos): string
    {
        $before = substr($code, 0, $pos);
        if (preg_match_all('/\/\*\*(?:[^*]|\*(?!\/))*?\*\//s', $before, $matches, PREG_OFFSET_CAPTURE)) {
            $lastMatch = end($matches[0]);
            $lastDoc = $lastMatch[0];
            $lastPos = $lastMatch[1] + strlen($lastDoc);
            $between = substr($before, $lastPos, $pos - $lastPos);
            if (preg_match('/^[\s\n\r\t\$\w=;]*$/', $between)) return $lastDoc;
        }
        return '';
    }

    protected static function parseDocBlock(?string $docBlock, array $keys = []): array
    {
        $data = [
            'description' => [],
            'params' => [],
            'return' => null,
            'context' => null,
            'examples' => [],
            'methods' => [],
            'properties' => [],
            'internal' => false,
            'see' => []
        ];

        if (!empty($docBlock) && str_starts_with(trim($docBlock), '/**')) {
            $clean = preg_replace(['/^\/\*\*/', '/\*\//', '/^\s*\*\s?/m'], '', $docBlock);
            $lines = explode("\n", trim($clean));
            $currentTag = null;

            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (preg_match('/^@([a-zA-Z0-9_-]+)\b/', $trimmedLine, $m)) {
                    $tag = $m[1];
                    $content = trim(substr($trimmedLine, strlen($m[0])));
                    $currentTag = $tag;

                    if ($tag == 'internal')
                        $data['internal'] = true;

                    if ($tag == 'category' || $tag == 'context')
                        $data['context'] = $content !== '' ? $content : null;

                    if ($tag == 'see')
                        if ($content !== '') $data['see'][] = $content;

                    if ($tag == 'param')
                        if (preg_match('/^([^\s]+)\s+\$(\w+)\s*(.*)$/', $content, $pm))
                            $data['params'][$pm[2]] = ['type' => $pm[1], 'description' => trim($pm[3])];

                    if ($tag == 'return')
                        if (preg_match('/^([^\s]+)\s*(.*)$/', $content, $rm))
                            $data['return'] = $rm[1];

                    if ($tag == 'method')
                        if (preg_match('/^(static\s+)?([^\s]+)\s+(\w+)\((.*?)\)\s*(.*)$/', $content, $mm)) {
                            $params = [];
                            if (!empty($mm[4])) {
                                $argList = array_map('trim', explode(',', $mm[4]));
                                foreach ($argList as $arg) {
                                    if (preg_match('/^([^\s]+)\s+(&)?\$(\w+)(?:\s*=\s*(.+))?/', $arg, $pm)) {
                                        $hasDefault = isset($pm[4]);
                                        $params[$pm[3]] = [
                                            'name' => $pm[3],
                                            'type' => $pm[1],
                                            'optional' => $hasDefault,
                                            'default' => $hasDefault ? trim($pm[4]) : null,
                                            'reference' => !empty($pm[2]),
                                        ];
                                    }
                                }
                            }
                            $data['methods'][$prm[2]] = [
                                'name' => $mm[3],
                                'static' => !empty($mm[1]),
                                'return' => $mm[2],
                                'params' => $params,
                                'description' => trim($mm[5])
                            ];
                        }

                    if ($tag == 'property')
                        if (preg_match('/^([^\s]+)\s+\$(\w+)\s*(.*)$/', $content, $prm)) {
                            $data['properties'][$prm[2]] = [
                                'name' => $prm[2],
                                'type' => $prm[1],
                                'description' => trim($prm[3])
                            ];
                        }


                    if ($tag == 'example')
                        $data['examples'][] = [$content];
                } else {
                    if ($currentTag === 'example' && !empty($data['examples']))
                        $data['examples'][count($data['examples']) - 1][] = $line;

                    if ($currentTag === null && $trimmedLine !== '')
                        $data['description'][] = $trimmedLine;
                }
            }
        }

        if (count($keys)) $data = array_intersect_key($data, array_flip($keys));

        return $data;
    }
}
