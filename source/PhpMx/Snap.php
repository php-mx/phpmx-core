<?php

namespace PhpMx;

/** Captura e restaura o estado de propriedades estáticas de classes registradas. */
class Snap
{
    protected static array $groups = [];
    protected static array $snaps = [];

    /**
     * Registra uma ou mais classes em um snap.
     *
     * @param string            $snap    Nome do snap
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
     * Opcionalmente registra classes antes de capturar.
     *
     * @param string            $snap    Nome do snap a criar
     * @param string|array<string> ...$classes Classes a registrar antes de capturar (opcional)
     */
    static function create(string $snap, string|array ...$classes): void
    {
        if ($classes)
            self::register($snap, ...$classes);

        self::$snaps[$snap] = [];

        foreach (self::$groups[$snap] ?? [] as $class) {
            $ref = new \ReflectionClass($class);
            $state = [];

            foreach ($ref->getProperties(\ReflectionProperty::IS_STATIC) as $prop)
                $state[$prop->getName()] = $prop->getValue();

            self::$snaps[$snap][$class] = $state;
        }
    }

    /**
     * Restaura o estado das classes ao que foi capturado no snap.
     *
     * @param string $snap Nome do snap a restaurar
     */
    static function restore(string $snap): void
    {
        foreach (self::$snaps[$snap] ?? [] as $class => $state) {
            $ref = new \ReflectionClass($class);

            foreach ($state as $name => $value) {
                $prop = $ref->getProperty($name);
                $prop->setValue(null, $value);
            }
        }
    }
}
