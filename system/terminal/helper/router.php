<?php

use PhpMx\Autodoc;
use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;

/** Lista as rotas registradas no projeto */
return new class {

    function __invoke($match = null, $method = null)
    {
        $defaultScheme = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];
        $key = $defaultScheme;
        $registredRoutes = [];
        $method = is_null($method) ? null : strtoupper($method);
        $useFilter = !is_blank($match) && $match != '*';

        foreach (Path::seekForDirs('system/router') as $path) {
            foreach (array_reverse(Dir::seekForFile($path, true)) as $file) {
                $origim = Autodoc::getOriginPath($path, 'system/router');
                $registredRoutes[$origim] = $registredRoutes[$origim] ?? $defaultScheme;

                foreach (Autodoc::getDocSchemeFileRoutes(path($path, $file)) as $schemeRoute) {
                    $template = $schemeRoute['ref'];

                    $response = '';

                    if ($schemeRoute['response']['type'] == 'status') {
                        $responseStauts = $schemeRoute['response']['code'];
                        $color = is_httpStatusError($responseStauts) ? 'ed' : 'wd';
                        $response = "[#c:$color,$responseStauts]";
                    } elseif ($schemeRoute['response']['type'] == 'controller') {
                        if ($schemeRoute['response']['callable']) {
                            $responseFile = $schemeRoute['response']['file'];
                            $responseMethod = $schemeRoute['response']['method'];
                            $responseLine = $schemeRoute['response']['line'];

                            $response = "[#c:sd,$responseFile:$responseLine][#c:sd,$responseMethod()]";
                        } elseif ($schemeRoute['response']['file']) {
                            $responseFile = $schemeRoute['response']['file'];
                            $responseMethod = $schemeRoute['response']['method'];
                            $response = "[#c:ed,$responseFile:$responseLine] [#c:e,$responseMethod()]";
                        } else {
                            $responseClass = $schemeRoute['response']['class'];
                            $response = "[#c:e,$responseClass]";
                        }
                    }

                    $currentRoute = [
                        'order' =>  $template,
                        'type' => $schemeRoute['response']['type'],
                        'template' => '/' . trim($template, '/'),
                        'response' => $response,
                        'middlewares' => empty($schemeRoute['middlewares']) ? '' : '[' . implode(', ', $schemeRoute['middlewares']) . '] ',
                        'description' => str_replace("\n", ' ', $schemeRoute['response']['description'] ?? ''),
                        'origim' => $schemeRoute['origin'],
                        'file' => $schemeRoute['file'],
                        'replaced' => $key[$schemeRoute['method']][$template] ?? false,
                        'method' => $schemeRoute['method'],
                    ];

                    $key[$schemeRoute['method']][$template] = true;

                    if ($useFilter)
                        if ($match == '/' && $template != '/')
                            continue;
                        else if (!str_starts_with(trim($template, '/'), trim($match, '/')) && !$this->checkRouteMatch([$match], $template))
                            continue;

                    $registredRoutes[$schemeRoute['origin']][$schemeRoute['method']][] = $currentRoute;
                }
            }
        }

        $originsLn = -1;

        foreach (array_reverse($registredRoutes) as $origin => $methods) {
            $count = 0;
            foreach ($methods as $routes) $count += count($routes);

            if (!$count) continue;

            if (++$originsLn) Terminal::echol();
            Terminal::echol('[#c:sb,#]', $origin);

            foreach (array_reverse($methods) as $curentMethod => $routes) {
                if (!is_null($method) && $curentMethod != $method) continue;
                if (empty($routes)) continue;
                $routes = $this->organize($routes);

                foreach (array_reverse($routes) as $route) {
                    Terminal::echol();
                    if (!$route['replaced']) {
                        $response = $route['response'];
                        Terminal::echol(" - [#c:dd,$curentMethod][#c:dd,:][#c:p,#template] $response", $route);
                        if ($route['type'] != 'status' && !empty($route['description']))
                            Terminal::echol("     [#description]", $route);
                    } else {
                        Terminal::echol(" - [#c:dd,$curentMethod][#c:sd,:][#c:pd,#template] [#c:wd,replaced]", $route);
                    }
                }
            }
        }

        if ($originsLn == -1)
            Terminal::echol('[#c:dd,- empty -]');
    }

    protected static function checkRouteMatch(array $path, string $template): bool
    {
        $path = array_shift($path);

        if (is_null($path)) return true;

        $path = explode('/', $path);
        $path = array_filter($path);

        $template = trim($template, '/');
        $template = explode('/', $template);

        while (count($template)) {
            $expected = array_shift($template);
            $received = array_shift($path) ?? '';
            if ($expected === '...') return true;
            if (is_blank($received) && !is_blank($expected)) return false;
            if ($expected !== '#' && $received !== $expected) return false;
        }

        return count($path) === 0;
    }

    protected static function organize(array $array): array
    {
        uasort($array, function ($itemA, $itemB) {
            $a = $itemA['order'];
            $b = $itemB['order'];

            $countA = substr_count($a, '/');
            $countB = substr_count($b, '/');

            if ($countA !== $countB) return $countB <=> $countA;

            $posA = strpos($a, '/');
            $posB = strpos($b, '/');

            if ($posA !== $posB) return $posB <=> $posA;

            $aParts = explode('/', $a);
            $bParts = explode('/', $b);

            $max = max(count($aParts), count($bParts));

            $peso = fn($part) => match ($part) {
                '...' => 2,
                '#' => 1,
                default => 0,
            };

            for ($i = 0; $i < $max; $i++) {
                $partA = $aParts[$i] ?? '';
                $partB = $bParts[$i] ?? '';

                $pesoA = $peso($partA);
                $pesoB = $peso($partB);

                if ($pesoA !== $pesoB) return $pesoA <=> $pesoB;
            }

            return 0;
        });

        return $array;
    }
};
