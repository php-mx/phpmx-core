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

        $commandDocContent = self::docBlockBefore($content, $pos);
        $commandDocScheme = self::parseDocBlock($commandDocContent);

        $reflectionParams = [];
        preg_match('/function\s+__invoke\s*\((.*?)\)(?:\s*:\s*([^\s{;]+))?/s', $content, $invokeMatch, PREG_OFFSET_CAPTURE);

        if (!empty($invokeMatch)) {
            $invokePos = $invokeMatch[0][1];
            $invokeDocContent = self::docBlockBefore($content, $invokePos);
            $invokeDocScheme = self::parseDocBlock($invokeDocContent);

            $commandDocScheme = self::mergeDoc($commandDocScheme, $invokeDocScheme);

            $paramsStr = trim($invokeMatch[1][0] ?? '');
            if (!empty($paramsStr)) {
                preg_match_all('/(?:([^\s,$]+)\s+)?(&)?(\.\.\.)?\$(\w+)(?:\s*=\s*([^,]+))?/', $paramsStr, $paramMatches, PREG_SET_ORDER);
                foreach ($paramMatches as $p) {
                    $name = trim($p[4]);
                    $reflectionParams[$name] = array_filter([
                        'name' => $name,
                        'type' => !empty($p[1]) ? trim($p[1]) : null,
                        'optional' => !empty($p[5]),
                        'variadic' => !empty($p[3]),
                        'reference' => !empty($p[2]),
                        'default' => !empty($p[5]) ? trim($p[5]) : null,
                    ]);
                }
                $reflectionData = ['params' => $reflectionParams];

                $commandDocScheme = self::mergeDoc($reflectionData, $commandDocScheme);
            }
            if (isset($invokeMatch[2]))
                $commandDocScheme = self::mergeDoc(['return' => trim($invokeMatch[2][0])], $commandDocScheme);
        }

        $command = explode('system/terminal/', $file);
        $command = array_pop($command);
        $command = substr($command, 0, -4);
        $command = str_replace(['/', '\\'], '.', $command);

        return array_filter([
            '_key' => md5("command:$command"),
            '_type' => 'command',
            '_file' => path($file),
            '_line' => substr_count(substr($content, 0, $pos), "\n") + 1,
            '_origin' => Path::origin($file),

            'name' => $command,
            ...$commandDocScheme,
        ]);
    }
}
