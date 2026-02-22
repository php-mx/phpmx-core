<?php

namespace PhpMx\Reflection;

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;

class ReflectionExampleFile extends ReflectionSourceFile
{
    static function scheme(string $file): array
    {
        $content = Import::content($file);

        $type = self::detectType($content);

        $name = File::getName($file);

        if ($type == 'narrative') $scheme = self::schemeNarrative($content);
        if ($type == 'class') $scheme = self::schemeClass($file, $content);

        if (empty($scheme)) return [];

        $scheme['key'] = "example:$name";
        $scheme['typeKey'] = $type;
        $scheme['name'] = $name;
        $scheme['origin'] = Path::origin($file);
        $scheme['file'] = path($file);

        return $scheme;
    }

    protected static function detectType(string $content): string
    {
        if (preg_match('/^\s*(?:abstract\s+|final\s+)?class\s+\w+/im', $content))
            return 'class';

        if (preg_match('/new\s+class/i', $content))
            return 'anonimous-class';

        return 'narrative';
    }

    protected static function schemeNarrative(string $content): array
    {
        $hasPHP = (bool) preg_match('/^<\?php/im', $content);

        $lines = preg_split('/\r\n|\n|\r/', $content);
        $result = [];
        $inBlock = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^<\?php$/i', $trimmed)) continue;

            if ($hasPHP) {
                if (!$inBlock && preg_match('/^\/\*/', $trimmed)) {
                    $inBlock = true;
                    $trimmed = trim(preg_replace('/^\/\*+\s*/', '', $trimmed));
                }

                if ($inBlock && str_contains($trimmed, '*/')) {
                    $inBlock = false;
                    $trimmed = trim(preg_replace('/\s*\*\/.*$/', '', $trimmed));
                }

                if ($inBlock) {
                    $trimmed = trim(preg_replace('/^\*\s?/', '', $trimmed));
                    $result[] = str_starts_with($trimmed, '>') ? $trimmed : $trimmed;
                    continue;
                }

                if (preg_match('/^(\/\/|#)\s?(.*)$/', $trimmed, $m)) {
                    $trimmed = trim($m[2]);
                    $result[] = str_starts_with($trimmed, '>') ? $trimmed : $trimmed;
                    continue;
                }

                if ($trimmed !== '') {
                    $result[] = '> ' . $trimmed;
                    continue;
                }

                $result[] = '';
            } else {
                $result[] = $trimmed;
            }
        }

        while (!empty($result) && trim($result[0]) === '') array_shift($result);
        while (!empty($result) && trim(end($result)) === '') array_pop($result);

        return ['description' => array_values(array_filter($result))];
    }

    protected static function schemeClass(string $file, string $content): array
    {
        preg_match('/namespace\s+([\w\\\\]+);/m', $content, $nsMatch);
        preg_match('/(?:abstract\s+|final\s+)?class\s+(\w+)/i', $content, $classMatch);

        if (!$classMatch) return [];

        $namespace = $nsMatch[1] ?? '';
        $className = $classMatch[1];
        $fullName = trim("$namespace\\$className", '\\');

        require_once $file;

        $reflection = new \ReflectionClass($fullName);

        $docBlock = $reflection->getDocComment();
        $docScheme = self::parseDocBlock($docBlock);

        return [
            'summary' => $docScheme['summary'],
            'description' => $docScheme['description'],
            'abstract' => $reflection->isAbstract(),
            'extends' => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
            'implements' => $reflection->getInterfaceNames(),
            'traits' => $reflection->getTraitNames(),
            'constants' => self::extractConstantsReflection($reflection),
            'properties' => self::extractPropertiesReflection($reflection, $docScheme['properties'] ?? []),
            'methods' => self::extractMethodsReflection($reflection, $docScheme['methods'] ?? []),
        ];
    }
}
