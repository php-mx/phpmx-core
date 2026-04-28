<?php

use PhpMx\Dir;
use PhpMx\Reflection\ReflectionCommandFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista e todos os comandos disponíveis no terminal.
 * @param string $filter Nome ou parte do nome de um comando para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke(?string $filter = null)
    {
        $this->handle(
            'system/terminal',
            $filter,
            function ($item) {
                Terminal::echol('   [#c:p,#name] [#c:sd,#_file][#c:sd,:][#c:sd,#_line]', $item);
                foreach ($item['description'] ?? [] as $description)
                    Terminal::echol("      $description");
                foreach ($item['variations'] as $variation)
                    Terminal::echol('         [#c:dd,php] mx [#] [#c:dd,#]', [$item['name'], $variation]);
            }
        );
    }

    protected function scan(string $path)
    {
        $items = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = ReflectionCommandFile::scheme(path($path, $item));
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
};
