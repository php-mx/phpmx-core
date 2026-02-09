<?php

namespace PhpMx;

use ReflectionMethod;

/** Classe utilitária para mapear e documentar projetos. */
abstract class Autodoc
{
    /** Carrega esquema de documentação das constantes de um arquivo */
    static function getDocSchemeHelperFileConstants(string $file): array
    {
        $content = Import::content($file);

        preg_match_all('/^\s*define\s*\(\s*[\'"]([\w_]+)[\'"]\s*,/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $scheme = [];
        foreach ($matches as $match) {
            $ref = $match[1][0];
            $pos = $match[0][1];

            $docBlock = self::getDocBefore($content, $pos);
            $parsed = $docBlock ? self::parseDoc($docBlock) : [];

            $scheme[] = [
                'ref' => $ref,
                'doc' => $parsed,
                'origin' => self::getOriginPath($file),
                'file' => $file,
                'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ];
        }

        return $scheme;
    }

    /** Carrega esquema de documentação das funções de um arquivo */
    static function getDocSchemeHelperFileFunctions(string $file): array
    {
        $content = Import::content($file);

        preg_match_all('/^\s*function\s+(\w+)/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $scheme = [];
        foreach ($matches as $match) {
            $ref = $match[1][0];
            $pos = $match[0][1];

            $docBlock = self::getDocBefore($content, $pos);
            $parsed = $docBlock ? self::parseDoc($docBlock) : [];

            $scheme[] = [
                'ref' => $ref,
                'doc' => $parsed,
                'origin' => self::getOriginPath($file),
                'file' => $file,
                'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ];
        }

        return $scheme;
    }

    /** Carrega esquema de documentação das variaveis de ambiente padrão de um arquivo */
    static function getDocSchemeHelperFileEnvironments(string $file): array
    {
        $content = Import::content($file);

        preg_match_all('/^\s*Env::default\s*\(\s*[\'"]([\w_]+)[\'"]\s*,\s*/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $scheme = [];
        foreach ($matches as $match) {
            $ref = $match[1][0];
            $pos = $match[0][1];

            $docBlock = self::getDocBefore($content, $pos);
            $parsed = $docBlock ? self::parseDoc($docBlock) : [];

            $scheme[] = [
                'ref' => $ref,
                'doc' => $parsed,
                'origin' => self::getOriginPath($file),
                'file' => $file,
                'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ];
        }

        return $scheme;
    }

    /** Carrega esquema de documentação de um arquivo de comando */
    static function getDocSchemeFileCommand(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $posNewClass = $match[0][1];

        $docBlock = self::getDocBefore($content, $posNewClass);
        $parsed = $docBlock ? self::parseDoc($docBlock) : [];

        $line = substr_count(substr($content, 0, $posNewClass), "\n") + 1;

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
                    $optional = !empty($p[2]);
                    $params[] = [
                        'name' => $name,
                        'optional' => $optional,
                    ];
                }
            }
        }

        return [
            'ref' => $ref,
            'doc' => $parsed,
            'params' => $params,
            'origin' => self::getOriginPath($file),
            'file' => $file,
            'line' => $line,
        ];
    }

    /** Carrega esquema de documentação de um arquivo de middleware */
    static function getDocSchemeFileMiddleware(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $posNewClass = $match[0][1];

        $docBlock = self::getDocBefore($content, $posNewClass);
        $parsed = $docBlock ? self::parseDoc($docBlock) : [];

        $line = substr_count(substr($content, 0, $posNewClass), "\n") + 1;

        $ref = explode('system/middleware/', $file);
        $ref = array_pop($ref);
        $ref = substr($ref, 0, -4);
        $ref = str_replace(['/', '\\'], '.', $ref);

        return [
            'ref' => $ref,
            'doc' => $parsed,
            'origin' => self::getOriginPath($file),
            'file' => $file,
            'line' => $line
        ];
    }

    /** Carrega esquema de documentação das rotas definidas em uma arquivo */
    static function getDocSchemeFileRoutes(string $file): array
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
            foreach ($templates as $data) {
                $response = self::formatRouteResponse($data[1]);
                $scheme[] = [
                    'ref' => $data[0],
                    'method' => $method,
                    'response' => $response,
                    'params' => $data[2] ?? [],
                    'middlewares' => $data[3] ?? [],
                    'origin' => self::getOriginPath($file),
                    'file' => $file,
                    'line' => null
                ];
            }

        return $scheme;
    }

    /** Carrega esquema de documentação de um arquivo de classe */
    static function getDocSchemeFileClass(string $file): array
    {
        return [];
    }

    /** Carrega esquema de documentação de um arquivo de trait */
    static function getDocSchemeFileTrait(string $file): array
    {
        return [];
    }

    /** Carrega esquema de documentação de um arquivo de interface */
    static function getDocSchemeFileInterface(string $file): array
    {
        return [];
    }

    static function getOriginPath($path): string
    {
        if (str_starts_with($path, 'vendor/')) {
            $path = strtolower($path);
            $path = explode('/', $path);
            return $path[1] . '-' . $path[2];
        }
        return 'current-project';
    }

    protected static function getDocBefore(string $code, int $pos): string
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

    protected static function parseDoc(string $docBlock): array
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
                                'type' => $pm[1],
                                'description' => trim($pm[3])
                            ];
                        }
                        break;
                    case 'return':
                        if (preg_match('/^([^\s]+(?:\s*\|\s*[^\s]+)*)\s*(.*)$/', $content, $rm)) {
                            $result['return'] = [
                                'type' => $rm[1],
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
                                'type' => $tm[1],
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

    protected static function formatRouteResponse($response): array
    {
        if (is_int($response)) {
            return [
                'type' => 'status',
                'code' => $response,
                'description' => env("STM_$response") ?? 'HTTP Status ' . $response,
            ];
        }

        $parts = is_array($response) ? $response : [$response];
        $controller = array_shift($parts);
        $method = array_shift($parts) ?? '__invoke';

        $info = [
            'type' => 'controller',
            'class' => $controller,
            'method' => $method,
            'callable' => false,
            'file' => null,
            'line' => null,
            'description' => '',
        ];

        if (class_exists($controller)) {
            if (method_exists($controller, $method)) {
                $refMethod = new ReflectionMethod($controller, $method);
                $info['file'] = path($refMethod->getFileName());
                $info['line'] = $refMethod->getStartLine();
                $info['description'] = self::parseDoc($refMethod->getDocComment())['description'] ?? '';
                $info['callable'] = true;
            } else {
                $reflection = new \ReflectionClass($controller);
                $info['file'] = path($reflection->getFileName());
            }
        }

        return $info;
    }
}
