<?php

namespace PhpMx\Reflection;

use PhpMx\File;
use PhpMx\Path;

class ReflectionExampleFile extends BaseReflectionFile
{
    static function scheme(string $file): array
    {
        $content = file_get_contents($file);
        $name = File::getName($file);
        $description = self::parseContent($content);

        return array_filter([
            '_key' => md5("example:$name"),
            '_type' => 'example',
            '_file' => path($file),
            '_origin' => Path::origin($file),
            'name' => $name,
            'description' => $description ?: null,
        ]);
    }

    protected static function parseContent(string $content): array
    {
        $lines = [];
        $pos = 0;
        $len = strlen($content);
        $inPhp = false;
        $buffer = '';

        while ($pos < $len) {
            if (!$inPhp) {
                $next = stripos($content, '<?php', $pos);
                if ($next === false) {
                    $buffer .= substr($content, $pos);
                    $pos = $len;
                } else {
                    $buffer .= substr($content, $pos, $next - $pos);
                    foreach (self::parseTextSegment($buffer) as $line) $lines[] = $line;
                    $buffer = '';
                    $inPhp = true;
                    $pos = $next + 5;
                }
            } else {
                $next = strpos($content, '?>', $pos);
                if ($next === false) {
                    $buffer .= substr($content, $pos);
                    $pos = $len;
                } else {
                    $buffer .= substr($content, $pos, $next - $pos);
                    foreach (self::parsePhpSegment($buffer) as $line) $lines[] = $line;
                    $buffer = '';
                    $inPhp = false;
                    $pos = $next + 2;
                }
            }
        }

        if ($buffer !== '') {
            foreach ($inPhp ? self::parsePhpSegment($buffer) : self::parseTextSegment($buffer) as $line)
                $lines[] = $line;
        }

        $collapsed = [];
        $prevBlank = false;
        foreach ($lines as $line) {
            $isBlank = $line === '';
            if ($isBlank && $prevBlank) continue;
            $collapsed[] = $line;
            $prevBlank = $isBlank;
        }
        $lines = $collapsed;

        $result = [];
        $i = 0;
        $total = count($lines);
        while ($i < $total) {
            if (str_starts_with($lines[$i], '> ')) {
                $block = [];
                while ($i < $total && str_starts_with($lines[$i], '> ')) {
                    $block[] = $lines[$i++];
                }
                while (!empty($block) && $block[0] === '> ') array_shift($block);
                while (!empty($block) && end($block) === '> ') array_pop($block);
                foreach ($block as $bl) $result[] = $bl;
            } else {
                $result[] = $lines[$i++];
            }
        }
        $lines = $result;

        while (!empty($lines) && $lines[0] === '') array_shift($lines);
        while (!empty($lines) && end($lines) === '') array_pop($lines);

        return $lines;
    }

    protected static function parseTextSegment(string $text): array
    {
        $rawLines = preg_split('/\r\n|\n|\r/', $text);
        $result = [];
        $i = 0;
        $total = count($rawLines);

        while ($i < $total) {
            $line = $rawLines[$i];
            $trimmed = rtrim($line);
            $ltrimmed = ltrim($trimmed);

            if (str_starts_with($ltrimmed, '> ')) {
                $result[] = $ltrimmed;
                $i++;
                continue;
            }

            if (str_starts_with($ltrimmed, '/*')) {
                [$block, $consumed] = self::collectBlock($rawLines, $i);
                foreach ($block as $bl) $result[] = $bl;
                $i += $consumed;
                continue;
            }

            if (preg_match('/^(\/\/|#)\s?(.*)$/', $ltrimmed, $m)) {
                $result[] = trim($m[2]);
                $i++;
                continue;
            }

            $result[] = $trimmed;
            $i++;
        }

        return $result;
    }

    protected static function parsePhpSegment(string $code): array
    {
        $rawLines = preg_split('/\r\n|\n|\r/', $code);
        $result = [];
        $depth = 0;
        $i = 0;
        $total = count($rawLines);

        while ($i < $total) {
            $line = $rawLines[$i];
            $trimmed = trim($line);
            $content = rtrim($line);


            if ($trimmed === '') {
                $result[] = '> ';
                $i++;
                continue;
            }

            if ($depth > 0) {
                $result[] = '> ' . $content;
                $depth += substr_count($trimmed, '{') - substr_count($trimmed, '}');
                if ($depth < 0) $depth = 0;
                $i++;
                continue;
            }

            if (str_starts_with($trimmed, '> ')) {
                $result[] = $trimmed;
                $i++;
                continue;
            }

            if (preg_match('/^(\/\/|#)(.*)$/', $trimmed, $m)) {
                $result[] = trim($m[2]);
                $i++;
                continue;
            }

            if (str_starts_with($trimmed, '/**')) {
                [$block, $consumed] = self::collectBlock($rawLines, $i);

                $next = $i + $consumed;
                while ($next < $total && trim($rawLines[$next]) === '') $next++;
                $nextLine = $next < $total ? trim($rawLines[$next]) : '';

                $isFollowedByCode = $nextLine !== ''
                    && !preg_match('/^(\/\/|#|\/\*|\?>)/', $nextLine);

                if ($isFollowedByCode) {
                    for ($j = $i; $j < $i + $consumed; $j++)
                        $result[] = '> ' . rtrim($rawLines[$j]);
                } else {
                    foreach ($block as $bl) $result[] = $bl;
                }

                $i += $consumed;
                continue;
            }

            if (str_starts_with($trimmed, '/*')) {
                [$block, $consumed] = self::collectBlock($rawLines, $i);
                foreach ($block as $bl) $result[] = $bl;
                $i += $consumed;
                continue;
            }

            $result[] = '> ' . $content;
            $depth += substr_count($trimmed, '{') - substr_count($trimmed, '}');
            if ($depth < 0) $depth = 0;
            $i++;
        }

        return $result;
    }

    protected static function collectBlock(array $rawLines, int $start): array
    {
        $lines = [];
        $consumed = 0;
        $i = $start;
        $total = count($rawLines);
        while ($i < $total) {
            $line = $rawLines[$i];
            $trimmed = trim($line);
            $consumed++;

            $hasClose = str_contains($trimmed, '*/');
            $relevant = $hasClose ? substr($trimmed, 0, strpos($trimmed, '*/')) : $trimmed;

            if ($i === $start) {
                $cleaned = trim(preg_replace('/^\/\*+\s*/', '', $relevant));
            } else {
                $cleaned = trim(preg_replace('/^\*\s?/', '', $relevant));
            }

            if ($cleaned !== '') $lines[] = $cleaned;
            $i++;

            if ($hasClose) break;
        }

        return [$lines, $consumed];
    }
}
