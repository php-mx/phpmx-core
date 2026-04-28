<?php

namespace PhpMx;

/** Captura e restaura o estado de propriedades estáticas de classes registradas. */
class Snap
{
    protected static array $groups = [];
    protected static array $snaps = [];
    protected static array $props = [];

    /**
     * Registra uma ou mais classes em um snap.
     *
     * @param string               $snap     Nome do snap
     * @param string|array<string> ...$classes Classes a registrar
     */
    static function register(string $snap, string|array ...$classes): void
    {
        self::$groups[$snap] ??= [];

        foreach ($classes as $class)
            foreach ((array) $class as $c)
                if (!in_array($c, self::$groups[$snap]))
                    self::$groups[$snap][] = $c;
    }

    /**
     * Captura o estado atual das propriedades estáticas de todas as classes registradas no snap.
     * Os objetos ReflectionProperty são criados aqui e reutilizados em restore().
     * Opcionalmente registra classes antes de capturar.
     *
     * @param string               $snap      Nome do snap a criar
     * @param string|array<string> ...$classes Classes a registrar antes de capturar (opcional)
     */
    static function create(string $snap, string|array ...$classes): void
    {
        if ($classes)
            self::register($snap, ...$classes);

        self::$snaps[$snap] = [];
        self::$props[$snap] = [];

        foreach (self::$groups[$snap] ?? [] as $class) {
            $state = [];
            $props = [];

            foreach ((new \ReflectionClass($class))->getProperties(\ReflectionProperty::IS_STATIC) as $prop) {
                $name = $prop->getName();
                $props[$name] = $prop;
                $state[$name] = $prop->getValue();
            }

            self::$snaps[$snap][$class] = $state;
            self::$props[$snap][$class] = $props;
        }
    }

    /**
     * Restaura o estado das classes ao que foi capturado no snap.
     * Usa os objetos ReflectionProperty criados no create() — sem custo de Reflection.
     *
     * @param string $snap Nome do snap a restaurar
     */
    static function restore(string $snap): void
    {
        foreach (self::$props[$snap] ?? [] as $class => $props) {
            $state = self::$snaps[$snap][$class];

            foreach ($props as $name => $prop)
                $prop->setValue(null, $state[$name]);
        }
    }
}
