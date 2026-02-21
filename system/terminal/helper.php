<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
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
                Terminal::echol('   [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
                foreach ($item['variations'] as $variation)
                    Terminal::echol('         [#c:dd,php] mx [#] [#c:dd,#]', [$item['name'], $variation]);
            }
        );
    }

    protected function scan($path)
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = $this->reflectionFile(path($path, $item));
            if (!empty($scheme)) {
                $formattedNames = [];
                $requiredCount = 0;

                foreach ($scheme['params'] ?? [] as $param) {
                    $name = ($param['variadic'] ? '...' : '') . '<' . $param['name']  . '>';
                    $formattedNames[] = $name;
                    if (!$param['optional']) $requiredCount++;
                }

                $variations = [];
                $totalParams = count($formattedNames);

                for ($i = $requiredCount; $i <= $totalParams; $i++) {
                    $slice = array_slice($formattedNames, 0, $i);
                    $variations[] = implode(" ", $slice);
                }

                if (empty($variations)) $variations = [''];

                $scheme['variations'] = array_values(array_unique(array_filter($variations, fn($v) => $v !== null)));
                $items[] = $scheme;
            }
        }

        return $items;
    }

    protected function reflectionFile(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);
        if (!$match) return [];

        $pos = $match[0][1];
        $docBlock = self::docBlockBefore($content, $pos);
        $docScheme = self::parseDocBlock($docBlock, ['description', 'params', 'return', 'examples', 'see', 'internal', 'context']);

        $command = explode('system/terminal/', $file);
        $command = array_pop($command);
        $command = substr($command, 0, -4);
        $command = str_replace(['/', '\\'], '.', $command);

        $orderedParams = [];

        preg_match('/function\s+__invoke\s*\((.*?)\)/s', $content, $invokeMatch);
        if (!empty($invokeMatch[1])) {
            $paramsStr = trim($invokeMatch[1]);
            if ($paramsStr !== '') {
                preg_match_all('/(?:([^\s,$]+)\s+)?(&)?(\.\.\.)?\$(\w+)(?:\s*=\s*([^,]+))?/', $paramsStr, $paramMatches, PREG_SET_ORDER);

                foreach ($paramMatches as $p) {
                    $name = trim($p[4]);
                    $optional = !empty($p[5]);
                    $variadic = !empty($p[3]);

                    $docParam = $docScheme['params'][$name] ?? [];

                    $orderedParams[] = [
                        'name'     => $name,
                        'optional' => $optional,
                        'variadic' => $variadic,
                        'type'     => $p[1] ?? ($docParam['type'] ?? null),
                        'reference' => !empty($p[2]),
                        'description' => $docParam['description'] ?? []
                    ];
                }
            }
        }

        $docScheme['params'] = $orderedParams;
        $docScheme['context'] = $docScheme['context'] ?? 'cli';

        return [
            'key' => "command:$command",
            'typeKey' => 'command',
            'name' => $command,
            'origin' => Path::origin($file),
            'file' => $file,
            'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ...$docScheme,
        ];
    }
};
