<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

    protected $terminalUsed = [];

    function __invoke()
    {
        foreach (Path::seekDirs('terminal') as $path) {
            $origin = $this->getOrigim($path);

            self::echo();
            self::echo('[[#]]', $origin);
            self::echoLine();

            foreach ($this->getCommandsIn($path, $origin) as $command) {
                self::echo(' - [#terminal] ([#file]) [[#status]]', $command);

                foreach ($command['variations'] as $variation)
                    self::echo('     php mx [#][#]', [$command['terminal'], $variation]);

                self::echo();
            };
        }
    }

    function getOrigim($path)
    {
        if ($path === 'terminal') return 'CURRENT-PROJECT';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }

    function getCommandsIn($path, $origin)
    {
        $commands = [];
        foreach (Dir::seekForFile($path, true) as $ref) {
            $terminal = path($ref);
            $terminal = substr($ref, 0, -4);
            $terminal = str_replace('/', '.', $terminal);

            $file = path($path, $ref);

            $command = Import::return($file);

            $variations = [''];

            $invoke = new ReflectionMethod($command, '__invoke');

            foreach ($invoke->getParameters() as $param) {
                $name = '<' . $param->getName() . '>';
                if (!$param->isOptional()) {
                    $variations[0] .= " $name";
                } else {
                    $variations[] = end($variations) . " $name";
                }
            }

            $this->terminalUsed[$terminal] = $this->terminalUsed[$terminal] ?? $origin;

            $commands[$terminal] = [
                'terminal' => $terminal,
                'file' => $file,
                'variations' => $variations,
                'status' => $this->terminalUsed[$terminal] == $origin ? 'ok' : 'replaced in ' . $this->terminalUsed[$terminal]
            ];
        }
        ksort($commands);
        return $commands;
    }
};
