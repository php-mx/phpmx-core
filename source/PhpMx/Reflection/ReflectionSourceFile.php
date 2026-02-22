<?php

namespace PhpMx\Reflection;

use PhpMx\Import;
use PhpMx\Path;
use ReflectionClass;

class ReflectionSourceFile extends BaseReflectionFile
{
    static function scheme(string $file): array
    {
        $content = Import::content($file);

        preg_match('/namespace\s+([\w\\\\]+);/m', $content, $nsMatch);
        preg_match('/^\s*(?:abstract\s+|final\s+)?(?:class|trait|interface)\s+(\w+)/im', $content, $nameMatch);

        if (!$nameMatch) return [];

        $reflection = new ReflectionClass(trim(($nsMatch[1] ?? '') . '\\' . $nameMatch[1], '\\'));
        $sourceName = $reflection->getName();

        $type = 'class';
        if ($reflection->isInterface()) $type = 'interface';
        if ($reflection->isTrait()) $type = 'trait';

        $docBlock = $reflection->getDocComment();
        $docScheme = self::parseDocBlock($docBlock);

        $constants = self::extractConstantsReflection($reflection);
        $properties = self::extractPropertiesReflection($reflection, $docScheme['properties'] ?? []);
        $methods = self::extractMethodsReflection($reflection, $docScheme['methods'] ?? []);

        return [
            'key' => "source:$sourceName",
            'typeKey' => $type,
            'name' => $sourceName,
            'origin' => Path::origin($file),
            'file' => path($reflection->getFileName()),
            'line' => $reflection->getStartLine(),
            'abstract' => $reflection->isAbstract(),
            'final' => $reflection->isFinal(),
            'extends' => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
            'interface' => $reflection->getInterfaceNames(),
            'traits' => $reflection->getTraitNames(),
            'constants' => $constants,
            'properties' => $properties,
            'methods' => $methods,
            ...array_diff_key($docScheme, array_flip(['properties', 'methods']))
        ];
    }

    protected static function extractConstantsReflection(ReflectionClass $reflection): array
    {
        $constants = [];
        foreach ($reflection->getReflectionConstants() as $const) {
            if ($const->getDeclaringClass()->getName() !== $reflection->getName()) continue;

            $name = $const->getName();
            $docScheme = self::parseDocBlock($const->getDocComment());

            $constants[$name] = [
                'name' => $name,
                'visibility' => $const->isPublic() ? 'public' : ($const->isProtected() ? 'protected' : 'private'),
                'file' => $reflection->getFileName(),
                ...$docScheme
            ];
        }
        return $constants;
    }

    protected static function extractPropertiesReflection(ReflectionClass $reflect, array $docProperties): array
    {
        $props = [];
        foreach ($reflect->getProperties() as $prop) {
            if ($prop->getDeclaringClass()->getName() !== $reflect->getName()) continue;

            $name = $prop->getName();
            $doc = $docProperties[$name] ?? [];

            $refType = $prop->hasType() ? strval($prop->getType()) : '';
            $docType = $doc['type'] ?? '';
            $finalType = $refType;

            if ($docType && (!in_array(strtolower($refType), self::PRIMITIVES) || !in_array(strtolower($docType), self::PRIMITIVES)))
                $finalType = $docType;

            $props[$name] = [
                'name' => $name,
                'type' => $finalType ?: null,
                'static' => $prop->isStatic(),
                'visibility' => $prop->isPublic() ? 'public' : ($prop->isProtected() ? 'protected' : 'private'),
                'description' => $doc['description'] ?? ''
            ];
        }
        return $props;
    }

    protected static function extractMethodsReflection(ReflectionClass $reflect, array $docMethods): array
    {
        $methods = [];
        foreach ($reflect->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $reflect->getName()) continue;

            $name = $method->getName();

            $reflectionParams = [];
            foreach ($method->getParameters() as $p) {
                $reflectionParams[] = [
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
                'return' => $method->hasReturnType() ? strval($method->getReturnType()) : null
            ];

            $doc = $docMethods[$name] ?? self::parseDocBlock($method->getDocComment());
            $merged = self::mergeDocMethod($reflectionData, $doc);

            $methods[$name] = [
                'name' => $name,
                'visibility' => $method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private'),
                'static' => $method->isStatic(),
                'abstract' => $method->isAbstract(),
                'final' => $method->isFinal(),
                'line' => $method->getStartLine(),
                ...$merged
            ];
        }
        return $methods;
    }
}
