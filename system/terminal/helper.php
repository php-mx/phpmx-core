<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class {

    protected $used = [];

    function __invoke($command = null)
    {
        foreach (Path::seekForDirs('system/terminal') as $n => $path) {
            $origin = $this->getOrigim($path);

            if ($n > 0) Terminal::echo();

            Terminal::echo('[#greenB:#]', $origin);

            foreach ($this->getCommandsIn($path, $origin) as $cmd) {
                if (is_null($command) || str_starts_with($cmd['terminal'], $command)) {
                    Terminal::echo('[#cyan:#terminal] [#blueD:#file][#yellowD:#status]', $cmd);
                    foreach ($cmd['variations'] as $variation)
                        Terminal::echo(' php [#whiteB:mx][#whiteB:#][#whiteD:#]', [$cmd['terminal'], $variation]);
                }
            };
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/terminal') return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }

    protected function getCommandsIn($path, $origin)
    {
        $commands = [];
        foreach (Dir::seekForFile($path, true) as $ref) {
            $terminal = path($ref);
            $terminal = substr($ref, 0, -4);
            $terminal = str_replace('/', '.', $terminal);

            $file = path($path, $ref);

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

            if ($this->used[$terminal] == $origin) {
                $commands[$terminal] = [
                    'terminal' => $terminal,
                    'file' => " $file",
                    'variations' => $variations,
                    'status' => ''
                ];
            } else {
                $commands[$terminal] = [
                    'terminal' => $terminal,
                    'file' => '',
                    'variations' => [],
                    'status' => 'replaced in ' . $this->used[$terminal]
                ];
            }
        }
        ksort($commands);
        return $commands;
    }
};
