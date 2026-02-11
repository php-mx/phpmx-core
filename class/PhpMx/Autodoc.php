<?php

namespace PhpMx;

use Reflection;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

/** Classe utilitária para mapear e documentar projetos. */
abstract class Autodoc
{
    /** Retorna o docScheme das constantes de um arquivo */
    static function docSchemesConstantFile(string $file): array
    {
        $content = Import::content($file);
        preg_match_all('/^\s*define\s*\(\s*[\'"]([\w_]+)[\'"]\s*,/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $scheme = [];
        foreach ($matches as $match) {
            $ref = $match[1][0];
            $pos = $match[0][1];
            $docBlock = self::docBlockBefore($content, $pos);
            $scheme[] = [
                'ref' => $ref,
                'origin' => self::originPath($file),
                'file' => $file,
                'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
                ...self::parseDocBlock($docBlock, ['description', 'examples'])
            ];
        }
        return $scheme;
    }

    /** Retorna o docScheme das funções de um arquivo */
    static function docSchemesFunctionFile(string $file): array
    {
        $content = Import::content($file);
        preg_match_all('/^\s*function\s+(\w+)/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $scheme = [];
        foreach ($matches as $match) {
            $functionName = $match[1][0];

            $reflection = new ReflectionFunction($functionName);
            $docBlock = self::parseDocBlock($reflection->getDocComment(), ['description', 'params', 'return', 'examples']);
            $params = [];
            foreach ($reflection->getParameters() as $p) {
                $name = $p->getName();
                $type = $p->hasType() ? (string)$p->getType() : ($docBlock['params'][$name]['type'] ?? 'mixed');
                $params[$name] = [
                    'name' => $name,
                    'type' => $type,
                    'description' => $docBlock['params'][$name]['description'] ?? '',
                    'optional' => $p->isOptional(),
                    'default' => $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null
                ];
            }
            $returnType = $reflection->hasReturnType() ? (string)$reflection->getReturnType() : ($docBlock['return']['type'] ?? 'mixed');

            $scheme[] = [
                'ref' => $functionName,
                'description' => $docBlock['description'],
                'params' => array_values($params),
                'return' => [
                    'type' => $returnType,
                    'description' => $docBlock['return']['description'] ?? ''
                ],
                'examples' => $docBlock['examples'],
                'line' => $reflection->getStartLine(),
                'file' => $reflection->getFileName()
            ];
        }
        return $scheme;
    }

    /** Retorna o docScheme das variaveis de ambiente em um arquivo */
    static function docSchemesEnvironmentsFile(string $file): array
    {
        $content = Import::content($file);

        preg_match_all('/^\s*Env::default\s*\(\s*[\'"]([\w_]+)[\'"]\s*,\s*/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $scheme = [];
        foreach ($matches as $match) {
            $ref = $match[1][0];
            $pos = $match[0][1];
            $docBlock = self::docBlockBefore($content, $pos);

            $scheme[] = [
                'ref' => $ref,
                'origin' => self::originPath($file),
                'file' => $file,
                'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
                ...self::parseDocBlock($docBlock, ['description'])
            ];
        }

        return $scheme;
    }

    /** Carrega esquema de documentação de um arquivo de comando */
    static function docSchemeCommandFile(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $pos = $match[0][1];
        $docBlock = self::parseDocBlock(self::docBlockBefore($content, $pos), ['description', 'params', 'return', 'examples']);

        $ref = explode('system/terminal/', $file);
        $ref = array_pop($ref);
        $ref = substr($ref, 0, -4);
        $ref = str_replace(['/', '\\'], '.', $ref);

        $params = [];

        preg_match('/function\s+__invoke\s*\((.*?)\)/s', $content, $invokeMatch);

        if (!empty($invokeMatch[1])) {
            $paramsStr = trim($invokeMatch[1]);
            if ($paramsStr !== '') {
                preg_match_all('/\$(\w+)(?:\s*=\s*([^,]+))?/', $paramsStr, $paramMatches, PREG_SET_ORDER);
                foreach ($paramMatches as $p) {
                    $name = trim($p[1]);
                    $params[$name] = [
                        'name' => $name,
                        'type' => $docBlock['params'][$name]['type'] ?? 'mixed',
                        'description' => $docBlock['params'][$name]['description'] ?? '',
                        'optional' => !empty($p[2]),
                        'default' => isset($p[2]) ? trim($p[2]) : null
                    ];
                }
            }
        }

        $docBlock['params'] = array_values($params);

        return [
            'ref' => $ref,
            'origin' => self::originPath($file),
            'file' => $file,
            'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ...$docBlock
        ];
    }

    /** Retorna o docScheme de um arquivo de middleware */
    static function docSchemeMiddlewareFile(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $pos = $match[0][1];

        $docBlock = self::docBlockBefore($content, $pos);

        $ref = explode('system/middleware/', $file);
        $ref = array_pop($ref);
        $ref = substr($ref, 0, -4);
        $ref = str_replace(['/', '\\'], '.', $ref);

        return [
            'ref' => $ref,
            'origin' => self::originPath($file),
            'file' => $file,
            'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ...self::parseDocBlock($docBlock, ['description'])
        ];
    }

    /** Retorna o docScheme das rotas definidas em um arquivo */
    static function docSchemeRouteFile(string $file): array
    {
        $scheme = [];

        Import::only($file);

        /** @var Router|mixed $interceptorRouter */
        $interceptorRouter = new class extends Router {
            function captureRoutes()
            {
                $routes = self::$ROUTE;
                self::$ROUTE = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];
                return $routes;
            }
        };

        foreach ($interceptorRouter->captureRoutes() as $method => $templates)
            foreach ($templates as $data)
                $scheme[] = [
                    'ref' => $data[0],
                    'origin' => self::originPath($file),
                    'file' => $file,
                    'line' => null,
                    'middlewares' => $data[3] ?? [],
                    'method' => $method,
                    'response' => self::formatRouteResponse($data[1]),
                ];

        return $scheme;
    }

    /** Retorna o docScheme de um arquivo php class, trait ou interface */
    static function docSchemeSourceFile(string $file): array
    {
        $content = Import::content($file);

        preg_match('/namespace\s+([\w\\\\]+);/m', $content, $nsMatch);
        preg_match('/^\s*(?:abstract\s+|final\s+)?(?:class|trait|interface)\s+(\w+)/im', $content, $nameMatch);

        if (!$nameMatch) return [];

        $namespace = $nsMatch[1] ?? '';
        $shortName = $nameMatch[1];
        $className = $namespace ? "$namespace\\$shortName" : $shortName;

        $reflection = new ReflectionClass($className);

        $type = 'class';
        if ($reflection->isInterface()) $type = 'interface';
        if ($reflection->isTrait()) $type = 'trait';

        $constants = self::extractConstantsReflection($reflection);
        $properties = self::extractPropertiesReflection($reflection);
        $methods = self::extractMethodsReflection($reflection);

        $classDoc = self::parseDocBlock($reflection->getDocComment(), ['description', 'examples', 'methods', 'properties']);

        $extends = $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null;
        $implements = $reflection->getInterfaceNames();
        $traits = $reflection->getTraitNames();

        foreach ($classDoc['methods'] as $name => $vMethod) {
            $exists = false;
            foreach ($methods as $m) {
                if ($m['name'] === $name) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $methods[] = [
                    'name' => $name,
                    'visibility' => 'public',
                    'static' => false,
                    'final' => false,
                    'line' => null,
                    'description' => [$vMethod['description']],
                    'params' => [
                        [
                            'name' => 'args',
                            'type' => $vMethod['args'],
                            'description' => 'Argumentos definidos via @method',
                            'optional' => true,
                            'default' => null
                        ]
                    ],
                    'return' => ['type' => $vMethod['type'], 'description' => ''],
                    'examples' => []
                ];
            }
        }

        foreach ($classDoc['properties'] as $name => $vProp) {
            $exists = false;
            foreach ($properties as $p) {
                if ($p['name'] === $name) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $properties[] = [
                    'name' => $name,
                    'visibility' => 'public',
                    'static' => false,
                    'line' => null,
                    'type' => $vProp['type'],
                    'description' => [$vProp['description']],
                ];
            }
        }

        return [
            'ref' => $reflection->getName(),
            'origin' => self::originPath($file),
            'file' => $reflection->getFileName(),
            'line' => substr_count(substr($content, 0, strpos($content, $shortName)), "\n") + 1,
            'type' => $type,
            'extends' => $extends,
            'implements' => $implements,
            'abstract' => $reflection->isAbstract(),
            'final' => $reflection->isFinal(),
            'uses' => $traits,
            'description' => $classDoc['description'],
            'examples' => $classDoc['examples'],
            'constants' => $constants,
            'properties' => $properties,
            'methods' => $methods,
        ];
    }

    /** Retorna o pacote de origem de um diretório ou arquivo */
    static function originPath($path): string
    {
        $path = path($path);
        if (str_starts_with($path, 'vendor/')) {
            $path = strtolower($path);
            $path = explode('/', $path);
            return $path[1] . '-' . $path[2];
        }
        return 'current-project';
    }

    protected static function docBlockBefore(string $code, int $pos): string
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

    protected static function parseDocBlock(?string $docBlock, array $keys = ['description', 'params', 'return', 'examples', 'methods', 'properties']): array
    {
        $data = [
            'description' => [],
            'params' => [],
            'return' => null,
            'examples' => [],
            'methods' => [],
            'properties' => []
        ];

        if (empty($docBlock) || !str_starts_with(trim($docBlock), '/**')) {
            return array_intersect_key($data, array_flip($keys));
        }

        $clean = preg_replace(['/^\/\*\*/', '/\*\//', '/^\s*\*\s?/m'], '', $docBlock);
        $lines = explode("\n", trim($clean));
        $currentTag = null;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (preg_match('/^@([a-zA-Z0-9_-]+)\b/', $trimmedLine, $m)) {
                $tag = $m[1];
                $content = trim(substr($trimmedLine, strlen($m[0])));
                $currentTag = $tag;

                switch ($tag) {
                    case 'param':
                        if (preg_match('/^([^\s]+)\s+\$(\w+)\s*(.*)$/', $content, $pm))
                            $data['params'][$pm[2]] = ['type' => $pm[1], 'description' => trim($pm[3])];
                        break;
                    case 'return':
                        if (preg_match('/^([^\s]+)\s*(.*)$/', $content, $rm))
                            $data['return'] = ['type' => $rm[1], 'description' => trim($rm[2])];
                        break;
                    case 'method':
                        if (preg_match('/^([^\s]+)\s+(\w+)\((.*?)\)\s*(.*)$/', $content, $mm))
                            $data['methods'][$mm[2]] = [
                                'type' => $mm[1],
                                'args' => $mm[3],
                                'description' => trim($mm[4])
                            ];
                        break;
                    case 'property':
                        if (preg_match('/^([^\s]+)\s+\$(\w+)\s*(.*)$/', $content, $prm))
                            $data['properties'][$prm[2]] = [
                                'type' => $prm[1],
                                'description' => trim($prm[3])
                            ];
                        break;
                    case 'example':
                        $data['examples'][] = [$content];
                        break;
                }
            } else {
                if ($currentTag === 'example' && !empty($data['examples']))
                    $data['examples'][count($data['examples']) - 1][] = $line;
                elseif ($currentTag === null && $trimmedLine !== '')
                    $data['description'][] = $trimmedLine;
            }
        }

        return array_intersect_key($data, array_flip($keys));
    }

    protected static function formatRouteResponse($response): array
    {
        if (is_int($response)) {
            return ['type' => 'status', 'code' => $response, 'description' => ''];
        }

        $parts = is_array($response) ? $response : [$response];
        $controller = array_shift($parts);
        $method = array_shift($parts) ?? '__invoke';

        $info = [
            'type' => 'class',
            'class' => $controller,
            'method' => $method,
            'description' => '',
            'callable' => false,
            'file' => null,
            'line' => null,
        ];

        if (class_exists($controller)) {
            if (method_exists($controller, $method)) {
                $refMethod = new ReflectionMethod($controller, $method);
                $info['file'] = path($refMethod->getFileName());
                $info['line'] = $refMethod->getStartLine();
                $info['callable'] = true;
                $info['description'] = self::parseDocBlock($refMethod->getDocComment(), ['description'])['description'];
            } else {
                $reflection = new ReflectionClass($controller);
                $info['file'] = path($reflection->getFileName());
            }
        }

        return $info;
    }

    protected static function extractConstantsReflection(ReflectionClass $reflect): array
    {
        $constants = [];
        foreach ($reflect->getReflectionConstants() as $const) {
            if ($const->getDeclaringClass()->getName() !== $reflect->getName()) continue;
            $constants[] = [
                'name' => $const->getName(),
                'value' => $const->getValue(),
                'visibility' => Reflection::getModifierNames($const->getModifiers())[0] ?? 'public',
                ...self::parseDocBlock($const->getDocComment()),
            ];
        }
        return $constants;
    }

    protected static function extractPropertiesReflection(ReflectionClass $reflect): array
    {
        $props = [];
        foreach ($reflect->getProperties() as $prop) {
            if ($prop->getDeclaringClass()->getName() !== $reflect->getName()) continue;

            $doc = self::parseDocBlock($prop->getDocComment(), ['description', 'return']);

            $props[] = [
                'name' => $prop->getName(),
                'visibility' => Reflection::getModifierNames($prop->getModifiers())[0] ?? 'public',
                'static' => $prop->isStatic(),
                'type' => $doc['return']['type'] ?? ($prop->hasType() ? (string)$prop->getType() : 'mixed'),
                'description' => $doc['description'],
            ];
        }
        return $props;
    }

    protected static function extractMethodsReflection(ReflectionClass $reflect): array
    {
        $methods = [];
        foreach ($reflect->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $reflect->getName()) continue;

            $parsedDoc = self::parseDocBlock($method->getDocComment(), ['description', 'params', 'return', 'examples']);

            $params = [];
            foreach ($method->getParameters() as $param) {
                $name = $param->getName();

                $type = $parsedDoc['params'][$name]['type'] ?? ($param->hasType() ? (string)$param->getType() : 'mixed');

                $params[] = [
                    'name' => $name,
                    'type' => $type,
                    'optional' => $param->isOptional(),
                    'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                    'reference' => $param->isPassedByReference(),
                    'description' => $parsedDoc['params'][$name]['description'] ?? ''
                ];
            }

            $returnType = $parsedDoc['return']['type'] ?? ($method->hasReturnType() ? (string)$method->getReturnType() : 'mixed');
            $returnDesc = $parsedDoc['return']['description'] ?? '';

            $methods[] = [
                'name' => $method->getName(),
                'visibility' => Reflection::getModifierNames($method->getModifiers())[0] ?? 'public',
                'static' => $method->isStatic(),
                'final' => $method->isFinal(),
                'line' => $method->getStartLine(),
                'static' => $method->isStatic(),
                'final' => $method->isFinal(),
                'abstract' => $method->isAbstract(),
                'description' => $parsedDoc['description'],
                'params' => $params,
                'return' => [
                    'type' => $returnType,
                    'description' => $returnDesc
                ],
                'examples' => $parsedDoc['examples']
            ];
        }
        return $methods;
    }
}
