<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista todas as funções utilitárias (helpers) registradas no sistema.
 * @param string filter Nome ou parte do nome de uma função para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/helper/function',
            $filter,
            function ($item) {
                Terminal::echol(' - [#c:p,#name][#c:p,()] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
            }
        );
    }

    protected function scan($path): array
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item)
            foreach ($this->getFileScheme(path($path, $item)) as $scheme)
                $items[] = $scheme;

        return $items;
    }

    protected function getFileScheme(string $file): array
    {
        $content = Import::content($file);
        $schemes = [];

        preg_match_all('/^\s*function\s+(\w+)/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $functionName = $match[1][0];

            $reflection = new ReflectionFunction($functionName);
            $docBlock = $reflection->getDocComment();
            $docScheme = $this->parseDocBlock($docBlock, ['description']);

            $schemes[] = [
                'key' => $functionName,
                'name' => $functionName,
                'file' => $reflection->getFileName(),
                'line' => $reflection->getStartLine(),
                ...$docScheme,
            ];
        }

        return $schemes;
    }
};
