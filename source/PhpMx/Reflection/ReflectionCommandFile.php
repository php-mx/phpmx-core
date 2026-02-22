<?php

namespace PhpMx\Reflection;

use PhpMx\Import;
use PhpMx\Path;

abstract class ReflectionCommandFile extends BaseReflectionFile
{
    static function scheme(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $pos = $match[0][1];

        $docBlock = self::docBlockBefore($content, $pos);
        $docScheme = self::parseDocBlock($docBlock);

        $reflectionParams = [];
        preg_match('/function\s+__invoke\s*\((.*?)\)/s', $content, $invokeMatch);

        if (!empty($invokeMatch[1])) {
            $paramsStr = trim($invokeMatch[1]);
            if ($paramsStr !== '') {
                preg_match_all('/(?:([^\s,$]+)\s+)?(&)?(\.\.\.)?\$(\w+)(?:\s*=\s*([^,]+))?/', $paramsStr, $paramMatches, PREG_SET_ORDER);

                foreach ($paramMatches as $p) {
                    $reflectionParams[] = [
                        'name' => trim($p[4]),
                        'type' => !empty($p[1]) ? trim($p[1]) : null,
                        'optional' => !empty($p[5]),
                        'variadic' => !empty($p[3]),
                        'reference' => !empty($p[2]),
                        'default' => !empty($p[5]) ? trim($p[5]) : null,
                    ];
                }
            }
        }

        $reflectionData = [
            'params' => $reflectionParams,
            'return' => null,
            'context' => 'cli'
        ];

        $mergedDoc = self::mergeDocMethod($reflectionData, $docScheme);

        $command = explode('system/terminal/', $file);
        $command = array_pop($command);
        $command = substr($command, 0, -4);
        $command = str_replace(['/', '\\'], '.', $command);

        return [
            'key' => "command:$command",
            'typeKey' => 'command',
            'name' => $command,
            'origin' => Path::origin($file),
            'file' => $file,
            'line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            ...$mergedDoc,
        ];
    }
}
