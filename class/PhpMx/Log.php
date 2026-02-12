<?php

namespace PhpMx;

use Closure;
use Throwable;

/**
 * Classe utilitária para registro estruturado de logs e escopos.
 * Permite o rastreamento de execução, medição de memória e aninhamento de mensagens.
 */
abstract class Log
{
    /** @ignore */
    protected static array $log = [];
    /** @ignore */
    protected static array $scope = [];
    /** @ignore */
    protected static bool $useLog = true;
    /** @ignore */
    protected static array $snap = ['log' => [], 'scope' => [], 'useLog' => true];

    /**
     * Captura um snapshot do estado atual do log.
     * @return void
     */
    static function snap(): void
    {
        self::$snap['log'] = self::$log;
        self::$snap['scope'] = self::$scope;
        self::$snap['useLog'] = self::$useLog;
    }

    /**
     * Restaura o log para o estado do último snapshot capturado.
     * @return void
     */
    static function reset(): void
    {
        self::$log = self::$snap['log'];
        self::$scope = self::$snap['scope'];
        self::$useLog = self::$snap['useLog'];
    }

    /**
     * Habilita ou desabilita o registro de logs.
     * @param bool $useLog
     * @return void
     */
    static function useLog(bool $useLog): void
    {
        self::$useLog = $useLog;
    }

    /**
     * Adiciona uma linha de log ou abre um escopo de execução via Closure.
     * @param string $type Categoria do log.
     * @param string $message Mensagem do log.
     * @param Closure|null $scope Closure opcional para criar um escopo de log.
     * @return mixed Retorno do Closure ou o resultado do log.
     */
    static function add(string $type, string $message, ?Closure $scope = null): mixed
    {
        if (!self::$useLog)
            return $scope();

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

    /**
     * Altera os dados da linha do escopo que está aberto no momento.
     * @param string $type Novo tipo/categoria.
     * @param string $message Nova mensagem.
     * @return void
     */
    static function changeScope(string $type, string $message): void
    {
        if (!self::$useLog) return;

        if (count(self::$scope)) {
            $scopeKey = end(self::$scope);
            self::$log[$scopeKey][0] = $type;
            self::$log[$scopeKey][1] = $message;
        }
    }

    /**
     * Registra uma exceção detalhada no log.
     * @param Throwable $e
     * @return void
     */
    static function exception(Throwable $e): void
    {
        if (!self::$useLog) return;

        $type = $e::class;
        $message = $e->getMessage();
        $file = path($e->getFile());
        $line = $e->getLine();

        self::set($type, "$message $file ($line)");
    }

    /**
     * Retorna o log processado com contadores de categorias.
     * @return array ['log' => array, 'count' => array]
     */
    static function get(): array
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

    /**
     * Retorna o log formatado em um array de strings.
     * @return array
     */
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

    /**
     * Retorna o log formatado como uma string completa.
     * @return string
     */
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

    /** @ignore */
    protected static function set(string $type, ?string $message = null, bool $isScope = false)
    {
        $scope = count(self::$scope);
        self::$log[] = [$type, $message, $scope, null];
    }

    /** @ignore */
    protected static function openScope(string $type, ?string $message = null)
    {
        self::set($type, $message);
        $index = count(self::$log) - 1;
        self::$log[$index][3] = memory_get_peak_usage(true);
        self::$scope[] = $index;
    }

    /** @ignore */
    protected static function closeScope()
    {
        if (count(self::$scope)) {
            $scopeKey = array_pop(self::$scope);
            self::closeLine(self::$log[$scopeKey]);
        }
    }

    /** @ignore */
    protected static function closeLine(&$line)
    {
        $line[3] = $line[3] ? memory_get_peak_usage(true) - $line[3] : null;
    }

    /** @ignore */
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
