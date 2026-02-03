<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista todas as funções de helper registradas no sistema */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/helper/function',
            $filter,
            ' - [#c:p,#ref] [#description]'
        );
    }

    protected function scan($path): array
    {
        $functions = [];
        foreach (Dir::seekForFile($path, true) as $item) {
            $file = path($path, $item);
            $content = Import::content($file);

            preg_match_all('/function\s+(\w+)/i', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

            foreach ($matches as $match) {
                $funcName = $match[1][0];
                $pos = $match[0][1];

                $description = $this->getDocBefore($content, $pos);

                $functions[] = [
                    'ref' => $funcName,
                    'description' => $description
                ];
            }
        }
        return $functions;
    }

    protected function getDocBefore(string $code, int $pos): string
    {
        $before = substr($code, 0, $pos);
        if (preg_match_all('/\/\*\*\s*(.*?)\s*\*\//s', $before, $docs)) {
            $lastDoc = end($docs[0]);
            $lastDesc = end($docs[1]);
            $lastPos = strrpos($before, $lastDoc) + strlen($lastDoc);

            if (preg_match('/^[\s\w\$\=]*$/', substr($before, $lastPos))) {
                return trim($lastDesc);
            }
        }
        return '';
    }
};
