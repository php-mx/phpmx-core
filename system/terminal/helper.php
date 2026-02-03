<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista e detalha todos os comandos disponíveis no terminal identificando parâmetros e origens */
return new class {

    use TerminalHelperTrait;

    function __invoke($filter = null)
    {
        $this->handle(
            'system/terminal',
            $filter,
            function ($item) {
                Terminal::echol(' - [#c:p,#ref] [#description]', $item);
                foreach ($item['variations'] as $variation)
                    Terminal::echol('    [#c:dd,php] mx [#][#c:dd,#]', [$item['ref'], $variation]);
            }
        );
    }

    protected function scan($path)
    {
        $commands = [];
        foreach (Dir::seekForFile($path, true) as $ref) {
            $terminal = substr($ref, 0, -4);
            $terminal = str_replace(['/', '\\'], '.', $terminal);

            $file = path($path, $ref);
            $content = Import::content($file);

            preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);
            $description = $match ? $this->getDocBefore($content, $match[0][1]) : '';

            $variations = [''];

            try {
                $command = Import::return($file);
                $invoke = new ReflectionMethod($command, '__invoke');
                foreach ($invoke->getParameters() as $param) {
                    $name = '<' . $param->getName() . '>';
                    if (!$param->isOptional()) {
                        $variations[0] .= " $name";
                    } else {
                        $variations[] = end($variations) . " $name";
                    }
                }
            } catch (Throwable) {
                $variations = [' <???>'];
            }

            $commands[$terminal] = [
                'ref' => $terminal,
                'description' => $description,
                'variations' => $variations,
            ];
        }
        return $commands;
    }

    protected function getDocBefore(string $code, int $pos): string
    {
        $before = substr($code, 0, $pos);
        if (preg_match_all('/\/\*\*\s*(.*?)\s*\*\//s', $before, $docs)) {
            $lastDoc = end($docs[0]);
            $lastDesc = end($docs[1]);
            $lastPos = strrpos($before, $lastDoc) + strlen($lastDoc);

            $between = substr($before, $lastPos);

            if (preg_match('/^[\s\w\$\=]*$/', $between)) {
                return trim($lastDesc);
            }
        }
        return '';
    }
};
