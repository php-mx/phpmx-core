<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista e detalha todos os comandos disponíveis no terminal do PhpMx.
 * @param string filter Nome ou parte do nome de um comando para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/terminal',
            $filter,
            function ($item) {
                Terminal::echol(' - [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
                foreach ($item['variations'] as $variation)
                    Terminal::echol('         [#c:dd,php] mx [#][#c:dd,#]', [$item['name'], $variation]);
            }
        );
    }

    protected function scan($path)
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = $this->getFileScheme(path($path, $item));
            if (!empty($scheme)) {
                $variations = [''];

                foreach ($scheme['params'] ?? [] as $param) {
                    $name = '<' . $param['name'] . '>';
                    if (!$param['optional'])
                        $variations[0] .= " $name";
                    if ($param['optional'])
                        $variations[] = end($variations) . " $name";
                }

                $scheme['variations'] = $variations;

                $items[] = $scheme;
            }
        }

        return $items;
    }

    protected function getFileScheme(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $pos = $match[0][1];

        $docBlock = $this->docBlockBefore($content, $pos);
        $docScheme = $this->parseDocBlock($docBlock, ['description', 'params']);

        $command = explode('system/terminal/', $file);
        $command = array_pop($command);
        $command = substr($command, 0, -4);
        $command = str_replace(['/', '\\'], '.', $command);

        preg_match('/function\s+__invoke\s*\((.*?)\)/s', $content, $invokeMatch);
        if (!empty($invokeMatch[1])) {
            $paramsStr = trim($invokeMatch[1]);
            if ($paramsStr !== '') {
                preg_match_all('/(?:([^\s,$]+)\s+)?(&)?\$(\w+)(?:\s*=\s*([^,]+))?/', $paramsStr, $paramMatches, PREG_SET_ORDER);
                foreach ($paramMatches as $p) {
                    $name = trim($p[3]);
                    $type = !empty($p[1]) ? trim($p[1]) : null;
                    $optional = !empty($p[4]);
                    $default = $optional ? trim($p[4]) : null;
                    $reference = !empty($p[2]);

                    $docScheme['params'][$name] = $docScheme['params'][$name] ?? ['name' => $name,  'description' => []];

                    $docScheme['params'][$name]['type'] = $docScheme['params'][$name]['type'] ?? $type ?? null;
                    $docScheme['params'][$name]['optional'] = $optional;
                    $docScheme['params'][$name]['default'] = $default;
                    $docScheme['params'][$name]['reference'] = $reference;
                }
            }
        }

        $docScheme['context'] = $docScheme['context'] ?? 'cli';

        return [
            'key' => $command,
            'name' => $command,
            'file' => $file,
            'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ...$docScheme,
        ];
    }
};
