<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista todas as constantes helper registradas no sistema */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/helper/constant',
            $filter,
            fn($item) => Terminal::echoln(' - [#c:p,#ref] [#description]', $item)
        );
    }

    protected function scan($path)
    {
        $constants = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $file = path($path, $item);
            $content = Import::content($file);

            preg_match_all('/(?:\/\*\*\s*(.*?)\s*\*\/\s*\n\s*)?define\(\s*[\'"](\w+)[\'"]/s', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $ref = $match[2];
                $constants[] = [
                    'ref' => $ref,
                    'description' => trim($match[1] ?? '')
                ];
            }
        }
        return $constants;
    }
};
