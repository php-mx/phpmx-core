<?php

namespace PhpMx\Reflection;

/** @ignore */
abstract class BaseReflectionFile
{
    const PRIMITIVES = ['int', 'integer', 'string', 'bool', 'boolean', 'float', 'double', 'array', 'object', 'callable', 'iterable', 'void', 'mixed', 'null'];

    abstract static function scheme(string $file): array;

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

    protected static function parseDocBlock(?string $docBlock): array
    {
        $data = [
            'summary' => null,
            'description' => [],
            'params' => [],
            'return' => null,
            'examples' => [],
            'methods' => [],
            'properties' => [],
            'internal' => false,
            'deprecated' => false,
            'since' => null,
            'throws' => [],
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

                    if ($tag == 'internal' || $tag == 'ignore')
                        $data['internal'] = true;

                    if ($tag == 'deprecated')
                        $data['deprecated'] = $content !== '' ? $content : true;

                    if ($tag == 'since')
                        $data['since'] = $content;

                    if ($tag == 'throws')
                        if (preg_match('/^([^\s]+)\s*(.*)$/', $content, $tm))
                            $data['throws'][] = ['type' => $tm[1], 'description' => trim($tm[2])];

                    if ($tag == 'see')
                        if ($content !== '') $data['see'][] = $content;

                    if ($tag == 'param') {
                        if (preg_match('/^([^\s]+)\s+(\.\.\.)?\$(\w+)\s*(.*)$/', $content, $pm)) {
                            $data['params'][$pm[3]] = [
                                'type' => $pm[1],
                                'variadic' => !empty($pm[2]),
                                'description' => trim($pm[4])
                            ];
                        }
                    }

                    if ($tag == 'return')
                        if (preg_match('/^([^\s]+)\s*(.*)$/', $content, $rm))
                            $data['return'] = $rm[1];

                    if ($tag == 'method') {
                        if (preg_match('/^(static\s+)?([^\s]+)\s+(\w+)\((.*?)\)\s*(.*)$/', $content, $mm)) {
                            $params = [];
                            if (!empty($mm[4])) {
                                $argList = array_map('trim', explode(',', $mm[4]));
                                foreach ($argList as $arg) {
                                    if (preg_match('/^([^\s]+)\s+(&)?(\.\.\.)?\$(\w+)(?:\s*=\s*(.+))?/', $arg, $ap)) {
                                        $hasDefault = isset($ap[5]);
                                        $params[$ap[4]] = [
                                            'name' => $ap[4],
                                            'type' => $ap[1],
                                            'optional' => $hasDefault,
                                            'default' => $hasDefault ? trim($ap[5]) : null,
                                            'reference' => !empty($ap[2]),
                                            'variadic' => !empty($ap[3]),
                                        ];
                                    }
                                }
                            }
                            $data['methods'][$mm[3]] = [
                                'name' => $mm[3],
                                'static' => !empty($mm[1]),
                                'return' => $mm[2],
                                'params' => $params,
                                'description' => trim($mm[5])
                            ];
                        }
                    }

                    if ($tag == 'property') {
                        if (preg_match('/^([^\s]+)\s+\$(\w+)\s*(.*)$/', $content, $prm)) {
                            $data['properties'][$prm[2]] = [
                                'name' => $prm[2],
                                'type' => $prm[1],
                                'description' => trim($prm[3])
                            ];
                        }
                    }

                    if ($tag == 'example') {
                        $data['examples'][] = [$content];
                    }
                } else {
                    if ($currentTag === 'example' && !empty($data['examples'])) {
                        $data['examples'][count($data['examples']) - 1][] = $line;
                    } else if ($currentTag === null && $trimmedLine !== '') {
                        $data['description'][] = $trimmedLine;
                        if ($data['summary'] === null)
                            $data['summary'] = $trimmedLine;
                    }
                }
            }
        }

        return $data;
    }

    protected static function mergeDocParams(array $reflectionParams, array $regexDocParams): array
    {
        $merged = [];
        foreach ($reflectionParams as $p) {
            $name = $p['name'];
            $doc = $regexDocParams[$name] ?? [];

            $refType = (string)($p['type'] ?? '');
            $docType = (string)($doc['type'] ?? '');
            $finalType = $refType;

            if ($docType && (!in_array(strtolower($refType), self::PRIMITIVES) || !in_array(strtolower($docType), self::PRIMITIVES))) {
                $finalType = $docType;
            }

            $merged[$name] = [
                'name' => $name,
                'type' => $finalType ?: null,
                'optional' => $p['optional'] ?? false,
                'variadic' => $p['variadic'] ?? false,
                'reference' => $p['reference'] ?? false,
                'default' => $p['default'] ?? null,
                'description' => $doc['description'] ?? ''
            ];
        }
        return $merged;
    }

    protected static function mergeDocMethod(array $reflectionData, array $regexDoc): array
    {
        $refRet = (string)($reflectionData['return'] ?? '');
        $docRet = (string)($regexDoc['return'] ?? '');

        $finalReturn = $refRet;
        if ($docRet && (!in_array(strtolower($refRet), self::PRIMITIVES) || !in_array(strtolower($docRet), self::PRIMITIVES))) {
            $finalReturn = $docRet;
        }

        return [
            'summary' => $regexDoc['summary'] ?? null,
            'description' => $regexDoc['description'] ?? [],
            'params' => self::mergeDocParams($reflectionData['params'] ?? [], $regexDoc['params'] ?? []),
            'return' => $finalReturn ?: null,
            'deprecated' => $regexDoc['deprecated'] ?? false,
            'since' => $regexDoc['since'] ?? null,
            'throws' => $regexDoc['throws'] ?? [],
            'see' => $regexDoc['see'] ?? [],
            'examples' => $regexDoc['examples'] ?? [],
            'internal' => $regexDoc['internal'] ?? false,
        ];
    }
}
