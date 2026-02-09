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
                $item['description'] = $item['description'] ?? '';
                $items[$p] = $item;
            }
            usort($items, fn($a, $b) => $a['ref'] <=> $b['ref']);
            $origins[Autodoc::getOriginPath($path, $scan)] = $items;
        }

        $origins = array_reverse($origins);

        foreach ($origins as $origin => $items) {

            if (!is_null($filter))
                $items = array_filter($items, fn($item) => is_null($filter) || str_starts_with(strtolower($item['filter']), strtolower($filter)));

            if (count($items)) {
                if (++$originsLn) Terminal::echol();
                Terminal::echol('[#c:sb,#]', $origin);
                foreach ($items as $item) {
                    !$item['replaced'] ? $echo($item) : $replaced($item);
                }
            }
        }
    }

    protected function getDocBeforeDescription(string $content, int $pos): string
    {
        $doc =  $this->getDocBefore($content, $pos);
        $description = $doc['description'] ?? '';
        $description = str_replace("\n", ' ', $description);
        return $description;
    }

    protected function getDocBefore(string $code, int $pos): string
    {
        $before = substr($code, 0, $pos);
        if (preg_match_all('/\/\*\*(?:[^*]|\*(?!\/))*?\*\//s', $before, $matches, PREG_OFFSET_CAPTURE)) {
            $lastMatch = end($matches[0]);
            $lastDoc = $lastMatch[0];
            $lastPos = $lastMatch[1] + strlen($lastDoc);
            $between = substr($before, $lastPos, $pos - $lastPos);
            if (preg_match('/^[\s\n\r\t\$\w=;]*$/', $between))
                return $lastDoc;
        }
        return '';
    }

    protected function parseDoc(string $docBlock): array
    {
        if (empty($docBlock) || !str_starts_with(trim($docBlock), '/**')) return [];
        $clean = preg_replace(['/^\/\*\*/', '/\*\//', '/^\s*\*\s?/m'], '', $docBlock);
        $clean = trim($clean);
        if (empty($clean)) return [];
        $lines = explode("\n", $clean);
        $result = [];
        $currentTag = null;
        $descriptionLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (preg_match('/^@([a-zA-Z0-9_-]+)\b/', $line, $m)) {
                $tag = $m[1];
                $content = trim(substr($line, strlen($m[0])));
                switch ($tag) {
                    case 'param':
                        if (preg_match('/^([^\s]+(?:\s*\|\s*[^\s]+)*)\s+\$(\w+)\s*(.*)$/', $content, $pm)) {
                            $result['params'] ??= [];
                            $result['params'][$pm[2]] = [
                                'type'        => $pm[1],
                                'description' => trim($pm[3])
                            ];
                        }
                        break;
                    case 'return':
                        if (preg_match('/^([^\s]+(?:\s*\|\s*[^\s]+)*)\s*(.*)$/', $content, $rm)) {
                            $result['return'] = [
                                'type'        => $rm[1],
                                'description' => trim($rm[2] ?? '')
                            ];
                        }
                        break;
                    case 'example':
                        $result['examples'] ??= [];
                        $result['examples'][] = $content;
                        $currentTag = 'example';
                        break;
                    case 'throws':
                    case 'throw':
                        if (preg_match('/^([^\s]+(?:\s*\|\s*[^\s]+)*)\s*(.*)$/', $content, $tm)) {
                            $result['throws'] ??= [];
                            $result['throws'][] = [
                                'type'        => $tm[1],
                                'description' => trim($tm[2] ?? '')
                            ];
                        }
                        break;
                    case 'see':
                        $result['see'] ??= [];
                        $result['see'][] = $content;
                        break;
                    case 'since':
                        $result['since'] = $content;
                        break;

                    case 'deprecated':
                        $result['deprecated'] = $content ?: true;
                        break;
                    case 'author':
                        $result['author'] ??= [];
                        $result['author'][] = $content;
                        break;
                    case 'version':
                        $result['version'] = $content;
                        break;
                    case 'internal':
                        $result['internal'] = true;
                        break;
                    default:
                        $result['other'] ??= [];
                        $result['other'][$tag] = $content;
                        break;
                }
                $currentTag = $tag === 'example' ? 'example' : null;
            } elseif ($currentTag === 'example') {
                $lastExample = &$result['examples'][count($result['examples']) - 1];
                $lastExample .= "\n" . $line;
            } else {
                $descriptionLines[] = $line;
            }
        }
        $description = trim(implode("\n", $descriptionLines));
        if ($description !== '') $result['description'] = $description;
        return $result;
    }
}
