<?php

namespace PhpMx;

abstract class Log
{
  protected static array $log = [];
  protected static bool $started = false;
  protected static array $count = ['mx' => 1];

  /** Inicia a captura do log */
  static function start($message, $prepare = [])
  {
    if (self::$started) return;

    self::$started = true;

    self::$log[] = [
      'type' => 'mx',
      'message' => prepare($message, $prepare),
      'isGroup' => true,
      'closed' => false,
      'time' => microtime(true),
      'memory' => memory_get_usage(),
      'lines' => [],
    ];
  }

  /** Interrompe a captura do log */
  static function stop(): void
  {
    if (!self::$started) return;

    while (self::currentLogGroup()) self::close(self::currentLogGroup());

    self::$started = false;
  }

  /** Retorna o log atual com contadores */
  static function get(): array
  {
    self::stop();
    return [
      'log' => self::$log,
      'count' => self::$count
    ];
  }

  /** Retorna o log em forma de array */
  static function getArray(): array
  {
    self::stop();
    $log = self::$log;

    $logArray = self::mountLogArray($log);

    $count = [];
    foreach (self::$count as $type => $n)
      if ($n)
        $count[$type] = $n;

    $logArray[] = $count;

    return $logArray;
  }

  /** Retorna o log em forma de string */
  static function getString(): string
  {
    self::stop();
    $log = self::$log;

    $logString = "-------------------------\n";
    $logString .= self::mountLogString($log);
    $logString .= "-------------------------\n";

    foreach (self::$count as $type => $n)
      if ($n)
        $logString .= "[$n] $type\n";

    $logString .= "-------------------------\n";

    return trim($logString);
  }

  /** Adicona ao log uma linha ou um escopo de linhas */
  static function add($type, $message, $prepare = [], $isGroup = false)
  {
    if (!self::$started) return;

    $log = &self::currentLogGroup();

    self::$count[$type] = self::$count[$type] ?? 0;
    self::$count[$type]++;

    $line = [
      'type' => $type,
      'message' => prepare($message, $prepare),
      'isGroup' => $isGroup,
    ];

    if ($isGroup) {
      $line['closed'] = false;
      $line['time'] = microtime(true);
      $line['memory'] = memory_get_usage(true);
      $line['lines'] = [];
    }

    $log['lines'][] = $line;
  }

  /** Fecha o escopo atual do logo */
  static function close()
  {
    if (!self::$started) return;
    $log = &self::currentLogGroup();

    $duration = microtime(true) - $log['time'];
    $memory = memory_get_usage() - $log['memory'];
    $log['closed'] = true;
    $log['time'] = $duration > 1 ? self::formatTime($duration) : '';
    $log['memory'] = $memory > 1 ?  self::formatMemory($memory) : '';
  }

  static protected function &currentLogGroup(?array &$group = null): ?array
  {

    if (is_null($group)) {
      $lastKey = array_key_last(self::$log);
      return self::currentLogGroup(self::$log[$lastKey]);
    }

    if ($group['closed']) {
      $null = null;
      return $null;
    }

    if (empty($group['lines'])) return $group;

    $lastKey = array_key_last($group['lines']);
    $last = &$group['lines'][$lastKey];

    if (!$last['isGroup']) return $group;

    $lastGroup = &self::currentLogGroup($last);

    if (is_null($lastGroup)) return $group;

    return self::currentLogGroup($last);
  }

  protected static function formatTime(float $seconds): string
  {
    if ($seconds < 1) return round($seconds * 1000, 2) . 'ms';
    if ($seconds < 60) return round($seconds, 2) . 's';
    if ($seconds < 3600) return round($seconds / 60, 2) . 'm';
    return round($seconds / 3600, 2) . 'h';
  }

  protected static function formatMemory(int $bytes): string
  {
    if ($bytes < 1024) return $bytes . 'b';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . 'kb';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . 'mb';
    return round($bytes / 1073741824, 2) . 'gb';
  }

  protected static function mountLogString(array $logGroup, int $level = 0): string
  {
    $output = '';
    $indent = str_repeat('| ', $level);

    foreach ($logGroup as $entry) {
      $type = $entry['type'];
      $message = $entry['message'];
      $time = $entry['time'] ? ' ' . $entry['time'] : '';
      $memory = $entry['memory'] ? ' ' . $entry['memory'] : '';
      $line = "[$type] $message$time$memory";
      $output .= "$indent$line\n";
      if ($entry['isGroup'] && !empty($entry['lines']))
        $output .= self::mountLogString($entry['lines'], $level + 1);
    }

    return $output;
  }

  protected static function mountLogArray(array $logGroup): array
  {
    $output = [];

    foreach ($logGroup as $entry) {
      $type = $entry['type'];
      $message = str_replace('\\', '.', $entry['message']);
      $time = $entry['time'] ? ' ' . $entry['time'] : '';
      $memory = $entry['memory'] ? ' ' . $entry['memory'] : '';
      $line = "[$type] $message$time$memory";
      $output[] = $line;
      if ($entry['isGroup'] && !empty($entry['lines']))
        $output[] = self::mountLogArray($entry['lines']);
    }

    return $output;
  }
}
