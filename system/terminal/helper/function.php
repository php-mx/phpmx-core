<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\ReflectionMxFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista todas as funções utilitárias (helpers) registradas no sistema.
 * @param string filter Nome ou parte do nome de uma função para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null, ...$teste)
    {
        $this->handle(
            'system/helper/function',
            $filter,
            function ($item) {
                Terminal::echol('   [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
                foreach ($item['variations'] as $variation)
                    Terminal::echol("         [#name][#c:dd,(]{$variation}[#c:dd,):][#c:pd,#return]", $item);
            }
        );
    }

    protected function scan($path): array
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item)
            foreach ($this->reflectionFile(path($path, $item)) as $scheme) {
                $variations = [''];

                foreach ($scheme['params'] ?? [] as $param) {
                    $name = '$' . $param['name'];
                    if ($param['reference']) $name = "[#c:s,&]$name";
                    if ($param['isVariadic']) $name = "[#c:s,...]$name";

                    $type = $param['type'];
                    $type = empty($type) ? '' : "[#c:pd,$type] ";

                    if (!$param['optional']) {
                        if (empty($variations[0]))
                            $variations[0] .= "$type$name";
                        else
                            $variations[0] .= "[#c:dd,#sep] $type$name";
                    }

                    if ($param['optional'])
                        if (empty(end($variations)))
                            $variations[] = "$type$name";
                        else
                            $variations[] .= end($variations) . "[#c:dd,#sep] $type$name";
                }

                $variations = array_map(fn($v) => trim(trim($v, ',')), $variations);

                $scheme['variations'] = $variations;
                $scheme['sep'] = ',';

                $items[] = $scheme;
            }

        return $items;
    }

    protected function reflectionFile(string $file): array
    {
        $content = Import::content($file);
        $schemes = [];

        preg_match_all('/^\s*function\s+(\w+)/im', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $functionName = $match[1];

            $reflection = new ReflectionFunction($functionName);
            $docBlock = $reflection->getDocComment();
            $docScheme = self::parseDocBlock($docBlock, ['description', 'params', 'return']);

            $orderedParams = [];

            foreach ($reflection->getParameters() as $p) {
                $name = $p->getName();

                $paramData = $docScheme['params'][$name] ?? [];

                $orderedParams[] = [
                    'name' => $name,
                    'type' => $p->hasType() ? strval($p->getType()) : ($paramData['type'] ?? null),
                    'optional' => $p->isOptional(),
                    'reference' => $p->isPassedByReference(),
                    'isVariadic' => $p->isVariadic(),
                    'description' => $paramData['description'] ?? []
                ];
            }

            $docScheme['params'] = $orderedParams;

            $returnType = $reflection->hasReturnType() ? strval($reflection->getReturnType()) : null;
            $docScheme['return'] = $docScheme['return'] ?? $returnType ?? null;

            $schemes[] = [
                'key' => "function:$functionName",
                'typeKey' => 'function',
                'name' => $functionName,
                'origin' => Path::origin($file),
                'file' => $reflection->getFileName(),
                'line' => $reflection->getStartLine(),
                ...$docScheme,
            ];
        }

        return $schemes;
    }
};
