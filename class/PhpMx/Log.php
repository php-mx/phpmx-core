<?php

namespace PhpMx;

use Closure;
use Throwable;

/** Classe utilitária para registro estruturado de logs e escopos. */
abstract class Log
{
    protected static array $log = [];
    protected static array $scope = [];
    protected static bool $useLog = true;

    protected static array $snap = ['log' => [], 'scope' => [], 'useLog' => true];

    /** Grava um snap do log */
    static function snap(): void
    {
        self::$snap['log'] = self::$log;
        self::$snap['scope'] = self::$scope;
        self::$snap['useLog'] = self::$useLog;
    }

    /** Restaura o log ao ultimo snap */
    static function reset(): void
    {
        self::$log = self::$snap['log'];
        self::$scope = self::$snap['scope'];
        self::$useLog = self::$snap['useLog'];
    }

    /** Define se o log deve ser utilizado */
    static function useLog(bool $useLog): void
    {
        self::$useLog = $useLog;
    }

    /** Adicona uma linha de log ou um escopo de linhas de log */
    static function add(string $type, string $message, ?Closure $scope = null): mixed
    {
        if (!self::$useLog)
            try {
                return $scope();
            } catch (Throwable $e) {
                throw $e;
            };

        if (is_null($scope))
            return self::set($type, $message);

        try {
            self::openScope($type, $message, true);
            $result = $scope();
            self::closeScope();
            return $result;
        } catch (Throwable $e) {
            self::exception($e);
            self::closeScope();
            throw $e;
        }
    }

    /** Altera a linha de escopo aberta */
    static function changeScope(string $type, string $message): void
    {
        if (!self::$useLog) return;

        if (count(self::$scope)) {
            $scopeKey = end(self::$scope);
            self::$log[$scopeKey][0] = $type;
            self::$log[$scopeKey][1] = $message;
        }
    }

    /** Adiciona uma linha de exceção ao log */
    static function exception(Throwable $e): void
    {
        if (!self::$useLog) return;

        $type = $e::class;
        $message = $e->getMessage();
        $file = path($e->getFile());
        $line = $e->getLine();

        self::set($type, "$message $file ($line)");
    }

    /** Retorna o log atual com contadores */
    static function get()
    {
        $currentLog = self::$log;
        $currentScope = self::$scope;

        $encapsLine = ['mx', 'log', -1, memory_get_peak_usage(true)];

        while (count($currentScope)) {
            $scopeKey = array_pop($currentScope);
            self::closeLine($currentLog[$scopeKey]);
        }

        array_unshift($currentLog, $encapsLine);

        $count = [];
        foreach ($currentLog as $pos => $line) {
            list($type, $message, $scope, $memory) = $line;

            $type = strToCamelCase($type);
            $message = str_replace('\\', '.', $message);
            $scope += 1;
            $memory = self::formatMemory($memory);

            $count[$type] = $count[$type] ?? 0;
            $count[$type]++;

            $currentLog[$pos] = [
                $type,
                $message,
                $scope,
                $memory,
            ];
        }

        return [
            'log' => $currentLog,
            'count' => $count
        ];
    }

    /** Retorna o log em forma de array */
    static function getArray(): array
    {
        $logData = self::get();
        $lines = $logData['log'];
        $count = $logData['count'];

        $output = [];

        foreach ($lines as $line) {
            list($type, $message, $scope, $memory) = $line;

            $info = [];

            if ($memory) $info[] = $memory;

            $infoText = count($info) ? ' [' . implode('|', $info) . ']' : '';

            $output[] = str_repeat('| ', $scope) . "[$type] $message$infoText";
        }

        $output[] = $count;

        return $output;
    }

    /** Retorna o log em forma de string */
    static function getString(): string
    {
        $logData = self::get();
        $lines = $logData['log'];
        $count = $logData['count'];

        $output = "-------------------------\n";

        foreach ($lines as $line) {
            list($type, $message, $scope, $memory) = $line;

            $info = [];

            if ($memory) $info[] = $memory;

            $info = count($info) ? ' [' . implode('|', $info) . ']' : '';

            $output .= str_repeat('| ', $scope) . "[$type] $message$info" . "\n";
        }

        $output .= "-------------------------\n";

        foreach ($count as $type => $qty)
            $output .= "[$qty] $type\n";

        $output .= "-------------------------\n";

        return trim($output);
    }

    protected static function set(string $type, ?string $message = null, bool $isScope = false)
    {
        $scope = count(self::$scope);
        self::$log[] = [$type, $message, $scope, null];
    }

    protected static function openScope(string $type, ?string $message = null)
    {
        self::set($type, $message);
        $index = count(self::$log) - 1;

        self::$log[$index][3] = memory_get_peak_usage(true);

        self::$scope[] = $index;
    }

    protected static function closeScope()
    {
        if (count(self::$scope)) {
            $scopeKey = array_pop(self::$scope);
            self::closeLine(self::$log[$scopeKey]);
        }
    }

    protected static function closeLine(&$line)
    {
        $line[3] = $line[3] ? memory_get_peak_usage(true) - $line[3] : null;
    }

    protected static function formatMemory(?int $bytes): ?string
    {
        if (is_null($bytes)) return null;

        if ($bytes < 1) return null;

        if ($bytes < 1024) return $bytes . 'b';

        if ($bytes < 1048576) return round($bytes / 1024, 2) . 'kb';

        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . 'mb';

        return round($bytes / 1073741824, 2) . 'gb';
    }
}
