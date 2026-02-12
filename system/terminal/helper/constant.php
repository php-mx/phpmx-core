<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista todas as constantes de ambiente registradas no sistema.
 * @param string filter Nome ou parte do nome de uma constante para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/helper/constant',
            $filter,
            function ($item) {
                Terminal::echol(' - [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
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

        preg_match_all('/^\s*define\s*\(\s*[\'"]([\w_]+)[\'"]\s*,/im', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $constantName = $match[1][0];
            $pos = $match[0][1];

            $docBlock = self::docBlockBefore($content, $pos);
            $docScheme = self::parseDocBlock($docBlock, ['description']);

            $schemes[] = [
                'key' => $constantName,
                'name' => $constantName,
                'file' => $file,
                'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
                '',
                ...$docScheme
            ];
        }

        return $schemes;
    }
};
