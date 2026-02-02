<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

/** Lista e detalha todos os comandos disponíveis no terminal identificando parâmetros e origens */
return new class {

    protected $used = [];

    function __invoke($command = null)
    {
        foreach (Path::seekForDirs('system/terminal') as $nPath => $path) {
            $origin = $this->getOrigim($path);

            if ($nPath > 0) Terminal::echo();

            Terminal::echo('[#greenB:#]', $origin);

            foreach ($this->getCommandsIn($path, $origin) as $nCommand => $cmd) {
                if (is_null($command) || str_starts_with($cmd['terminal'], $command)) {

                    if ($nCommand > 0) Terminal::echo();

                    Terminal::echo('[#cyan:#terminal] [#whiteD:#description] [#yellowD:#status]', $cmd);

                    Terminal::echo(' [#blueD:#file]', $cmd);

                    foreach ($cmd['variations'] as $variation)
                        Terminal::echo('  php [#whiteB:mx] [#whiteB:#][#whiteD:#]', [$cmd['terminal'], $variation]);
                }
            };
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/terminal') return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return ($parts[1] ?? 'unknown') . '-' . ($parts[2] ?? 'unknown');
        }

        return 'unknown';
    }

    protected function getCommandsIn($path, $origin)
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
            $this->used[$terminal] = $this->used[$terminal] ?? $origin;

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
                'terminal' => $terminal,
                'description' => $description,
                'file' => $file,
                'variations' => $variations,
                'status' => $this->used[$terminal] == $origin ? '' : 'replaced in ' . $this->used[$terminal]
            ];
        }
        ksort($commands);
        return $commands;
    }

    /** Extrai o DocBlock permitindo espaços e declarações de variáveis entre o comentário e a classe */
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
