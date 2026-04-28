<?php

use PhpMx\Dir;
use PhpMx\Reflection\ReflectionHelperFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista todas as helpers de funções registradas no sistema.
 * @param string $filter Nome ou parte do nome de uma função para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke(?string $filter = null)
    {
        $this->handle(
            'system/helper/function',
            $filter,
            function ($item) {
                Terminal::echol('   [#c:p,#name] [#c:sd,#_file][#c:sd,:][#c:sd,#_line]', $item);
                foreach ($item['description'] ?? [] as $description)
                    Terminal::echol("      $description");
                foreach ($item['variations'] as $variation)
                    Terminal::echol("         [#name][#c:dd,(]{$variation}[#c:dd,)][#c:pd,#return]", $item);
            }
        );
    }

    protected function scan(string $path): array
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item)
            foreach (ReflectionHelperFile::schemeFunctions(path($path, $item)) as $scheme) {
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
                $scheme['return'] =  $scheme['return'] ? ":" . $scheme['return'] : '';

                $items[] = $scheme;
            }

        return $items;
    }
};
