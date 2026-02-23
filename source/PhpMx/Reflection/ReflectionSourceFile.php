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

        return array_filter([
            '_key' => md5("source:$sourceName"),
            '_type' => $type,
            '_file' => path($reflection->getFileName()),
            '_line' => $reflection->getStartLine(),
            '_origin' => Path::origin($file),

            'name' => $sourceName,
            'abstract' => $reflection->isAbstract(),
            'final' => $reflection->isFinal(),
            'extends' => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
            'interface' => $reflection->getInterfaceNames(),
            'traits' => $reflection->getTraitNames(),
            'constants' => $constants,
            'properties' => $properties,
            'methods' => $methods,
            ...array_diff_key($docScheme, array_flip(['properties', 'methods']))
        ]);
    }

    protected static function extractConstantsReflection(ReflectionClass $reflection): array
    {
        $constants = [];
        foreach ($reflection->getReflectionConstants() as $const) {
            if ($const->getDeclaringClass()->getName() !== $reflection->getName()) continue;

            $name = $const->getName();
            $docScheme = self::parseDocBlock($const->getDocComment());

            $constants[$name] = array_filter([
                'name' => $name,
                'visibility' => $const->isPublic() ? 'public' : ($const->isProtected() ? 'protected' : 'private'),
                ...$docScheme
            ]);
        }

        return array_filter($constants);
    }

    protected static function extractPropertiesReflection(ReflectionClass $reflect, array $docProperties): array
    {
        $props = [];

        foreach ($reflect->getProperties() as $prop) {
            if ($prop->getDeclaringClass()->getName() !== $reflect->getName()) continue;

            $name = $prop->getName();

            $reflectionData = [
                'name' => $name,
                'type' => $prop->hasType() ? strval($prop->getType()) : null,
            ];

            $props[$name] = array_filter([
                ...$reflectionData,
                'static' => $prop->isStatic(),
                'visibility' => $prop->isPublic() ? 'public' : ($prop->isProtected() ? 'protected' : 'private'),
            ]);
        }

        $props = self::mergeDoc($props, $docProperties);

        return array_filter($props);
    }

    protected static function extractMethodsReflection(ReflectionClass $reflect, array $docMethods): array
    {
        $methods = [];
        foreach ($reflect->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $reflect->getName()) continue;

            $name = $method->getName();

            $reflectionParams = [];
            foreach ($method->getParameters() as $p) {
                $reflectionParams[$p->getName()] = array_filter([
                    'name' => $p->getName(),
                    'type' => $p->hasType() ? strval($p->getType()) : null,
                    'optional' => $p->isOptional(),
                    'variadic' => $p->isVariadic(),
                    'reference' => $p->isPassedByReference(),
                    'default' => $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null,
                ]);
            }

            $reflectionData = array_filter([
                'params' => array_filter($reflectionParams),
                'return' => $method->hasReturnType() ? strval($method->getReturnType()) : null
            ]);

            $methods[$name] = array_filter([
                'name' => $name,
                'visibility' => $method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private'),
                'static' => $method->isStatic(),
                'abstract' => $method->isAbstract(),
                'final' => $method->isFinal(),
                'line' => $method->getStartLine(),
                ...$reflectionData
            ]);
        }

        $methods = self::mergeDoc($methods, $docMethods);

        return array_filter($methods);
    }
}
