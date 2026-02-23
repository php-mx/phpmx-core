<?php

namespace PhpMx\Reflection;

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use ReflectionClass;

class ReflectionExampleFile extends ReflectionSourceFile
{
    static function scheme(string $file): array
    {
        $content = Import::content($file);

        $type = self::detectType($content);

        $name = File::getName($file);

        $scheme = $type == 'implement' ? self::schemeImplement($file, $content) : self::schemeNarrative($content);

        if ($type == 'implement')
            dd($scheme);


        $metaScheme = [
            '_key' => md5("example:$name"),
            '_type' => $type,
            '_file' => path($file),
            '_origin' => Path::origin($file),

            'name' => $name,
        ];

        return array_filter(self::mergeDoc($metaScheme, $scheme));
    }

    protected static function detectType(string $content): string
    {
        if (preg_match('/^\s*(?:abstract\s+|final\s+)?class\s+\w+/im', $content))
            return 'implement';

        if (preg_match('/new\s+class/i', $content))
            return 'implement';

        return 'narrative';
    }

    protected static function schemeNarrative(string $content): array
    {
        $hasPHP = (bool) preg_match('/^<\?php/im', $content);

        $lines = preg_split('/\r\n|\n|\r/', $content);
        $result = [];
        $inBlock = false;

        foreach ($lines as $line) {
            $trimmed = $line;

            if (preg_match('/^<\?php$/i', $trimmed)) continue;

            if ($hasPHP) {
                if (!$inBlock && preg_match('/^\/\*/', $trimmed)) {
                    $inBlock = true;
                    $trimmed = trim(preg_replace('/^\/\*+\s*/', '', $trimmed));
                }

                if ($inBlock && str_contains($trimmed, '*/')) {
                    $inBlock = false;
                    $trimmed = trim(preg_replace('/\s*\*\/.*$/', '', $trimmed));
                    if ($trimmed !== '') $result[] = $trimmed;
                    continue;
                }

                if ($inBlock) {
                    $trimmed = trim(preg_replace('/^\*\s?/', '', $trimmed));
                    $result[] = $trimmed;
                    continue;
                }

                if (preg_match('/^(\/\/|#)\s?(.*)$/', $trimmed, $m)) {
                    $result[] = trim($m[2]);
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

        return ['description' => $result];
    }

    protected static function schemeImplement(string $file, string $content): array
    {
        $fileReturn = Import::return($file);

        if (is_object($fileReturn)) {
            $reflection = new \ReflectionClass($fileReturn);
        } else {
            preg_match('/namespace\s+([\w\\\\]+);/m', $content, $nsMatch);
            preg_match('/(?:abstract\s+|final\s+)?class\s+(\w+)/i', $content, $classMatch);

            if (!$classMatch) return [];

            $namespace = $nsMatch[1] ?? '';
            $className = $classMatch[1];
            $fullName = trim("$namespace\\$className", '\\');

            $reflection = new \ReflectionClass($fullName);
        }

        $docBlock = $reflection->getDocComment();
        $docScheme = self::parseDocBlock($docBlock);

        return array_filter([
            'description' => $docScheme['description'] ?? null,
            'abstract' => $reflection->isAbstract(),
            'anonymous' => is_object($fileReturn),
            'final' => $reflection->isFinal(),
            'extends' => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
            'implements' => $reflection->getInterfaceNames(),
            'traits' => $reflection->getTraitNames(),
            'constants' => self::extractConstantsReflection($reflection),
            'properties' => self::extractPropertiesReflection($reflection, $docScheme['properties'] ?? []),
            'methods' => self::extractMethodsReflection($reflection, $docScheme['methods'] ?? []),
        ]);
    }

    protected static function extractMethodsReflection(ReflectionClass $reflect, array $docMethods): array
    {
        $methods = parent::extractMethodsReflection($reflect, $docMethods);

        $file = $reflect->getFileName();
        $fileLines = file($file);

        foreach ($reflect->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $reflect->getName()) continue;

            $name = $method->getName();
            if (!isset($methods[$name])) continue;

            $start = $method->getStartLine();
            $end = $method->getEndLine();
            $bodyLines = array_slice($fileLines, $start, $end - $start - 1);

            $bodyLines = array_values($bodyLines);
            if (!empty($bodyLines) && trim($bodyLines[0]) === '{')
                array_shift($bodyLines);

            $minIndent = PHP_INT_MAX;
            foreach ($bodyLines as $line) {
                if (trim($line) === '') continue;
                preg_match('/^(\s*)/', $line, $m);
                $minIndent = min($minIndent, strlen($m[1]));
            }
            if ($minIndent === PHP_INT_MAX) $minIndent = 0;

            $body = [];
            foreach ($bodyLines as $line) {
                $body[] = rtrim(substr($line, $minIndent));
            }

            while (!empty($body) && trim($body[0]) === '') array_shift($body);
            while (!empty($body) && trim(end($body)) === '') array_pop($body);

            if (!empty($body))
                $methods[$name]['implementation'] = array_values(array_filter($body));
        }

        return array_filter($methods);
    }
}
