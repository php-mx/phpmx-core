<?php

namespace PhpMx\Reflection;

use PhpMx\Import;
use PhpMx\Path;
use ReflectionFunction;

class ReflectionHelperFile extends BaseReflectionFile
{
    static function scheme(string $file): array
    {
        return [
            'constant' => self::schemeConstants($file),
            'function' => self::schemeFunctions($file),
            'environment' => self::schemeEnvironments($file),
        ];
    }

    static function schemeConstants(string $file): array
    {
        $content = Import::content($file);
        $schemes = [];

        preg_match_all('/^\s*define\s*\(\s*[\'"]([\w_]+)[\'"]\s*,/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $constantName = $match[1][0];
            $pos = $match[0][1];

            $docBlock = self::docBlockBefore($content, $pos);
            $docScheme = self::parseDocBlock($docBlock);

            $schemes[] = array_filter([
                '_key' => md5("constant:$constantName"),
                '_type' => 'constant',
                '_file' => path($file),
                '_line' => substr_count(substr($content, 0, $pos), "\n") + 1,
                '_origin' => Path::origin($file),

                'name' => $constantName,
                'call' => $constantName,
                ...$docScheme
            ]);
        }

        return array_filter($schemes);
    }

    static function schemeFunctions(string $file): array
    {
        $content = Import::content($file);
        $schemes = [];

        preg_match_all('/^\s*function\s+(\w+)/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $functionName = $match[1][0];

            $reflection = new ReflectionFunction($functionName);
            $docBlock = $reflection->getDocComment();
            $docScheme = self::parseDocBlock($docBlock);

            $reflectionParams = [];
            foreach ($reflection->getParameters() as $p) {
                $reflectionParams[$p->getName()] = [
                    'name' => $p->getName(),
                    'type' => $p->hasType() ? strval($p->getType()) : null,
                    'optional' => $p->isOptional(),
                    'variadic' => $p->isVariadic(),
                    'reference' => $p->isPassedByReference(),
                    'default' => $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null,
                ];
            }

            $reflectionData = [
                'params' => $reflectionParams,
                'return' => $reflection->hasReturnType() ? strval($reflection->getReturnType()) : null
            ];

            $mergedDoc = self::mergeDoc($reflectionData, $docScheme);

            $schemes[] = array_filter([
                '_key' => md5("function:$functionName"),
                '_type' => 'function',
                '_file' => path($reflection->getFileName()),
                '_line' => $reflection->getStartLine(),
                '_origin' => Path::origin($file),

                'name' => $functionName,
                'call' => "$functionName()",
                ...$mergedDoc,
            ]);
        }

        return array_filter($schemes);
    }

    static function schemeEnvironments(string $file): array
    {
        $content = Import::content($file);
        $schemes = [];

        preg_match_all('/^\s*Env::default\s*\(\s*[\'"]([\w_]+)[\'"]\s*,\s*/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $environmentsName = $match[1][0];
            $pos = $match[0][1];

            $docBlock = self::docBlockBefore($content, $pos);
            $docScheme = self::parseDocBlock($docBlock);

            $docScheme['see'][] = 'env()';

            $schemes[] = array_filter([
                '_key' => md5("environment:$environmentsName"),
                '_type' => 'environment',
                '_file' => path($file),
                '_line' => substr_count(substr($content, 0, $pos), "\n") + 1,
                '_origin' => Path::origin($file),

                'name' => $environmentsName,
                'call' => "env('$environmentsName')",
                ...$docScheme
            ]);
        }

        return array_filter($schemes);
    }
}
