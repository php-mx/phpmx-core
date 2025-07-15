<?php

namespace PhpMx;

abstract class Cookie
{
    /** Lista de cookies controlados */
    protected static $COOKIES = [];

    /** Lista de cookies removidos na requisição */
    protected static $UNLINKED = [];

    /** Retorna o valor de um cookie */
    static function get(string $name): ?string
    {
        $safe = str_starts_with($name, '#');

        if ($safe) $name = mx5($name);

        if (!isset(self::$COOKIES[$name]))
            self::$COOKIES[$name] = self::__getCookie($name);

        $value = self::$COOKIES[$name] ?? null;

        if ($safe) {
            $value = Cif::check($value) ? Cif::off($value) : null;
            if (!is_string($value) || !str_starts_with($value, 'Cookie:'))
                return null;
            $value = substr($value, 7);
        }

        return $value;
    }

    /** Define um valor para um cookie */
    static function set(string $name, ?string $value): void
    {
        $safe = str_starts_with($name, '#');

        if ($safe) {
            $name = mx5($name);
            $value = Cif::on("Cookie:$value");
        }

        if (!is_null($value)) {
            if (isset(self::$UNLINKED[$name]))
                unset(self::$UNLINKED[$name]);

            self::$COOKIES[$name] = $value;
            self::__setCookie($name, $value);
        } else {
            self::$UNLINKED[$name] = true;
            self::__setCookie($name, '', "-1 days");
        }
    }

    /** Verifica se um cookie existe ou se tem um valor igual ao fornecido */
    static function check(string $name): bool
    {
        return !is_null(self::get($name));
    }

    /** Remove um cookie */
    static function remove(string $name): void
    {
        self::set($name, null);
    }

    /** Captura o valor de um cookie real do PHP */
    protected static function __getCookie(string $name): mixed
    {
        return filter_input(INPUT_COOKIE, $name);
    }

    /** Altera o valor de um cookie real do PHP */
    protected static function __setCookie(string $name, mixed $value, ?string $time = null): void
    {
        $time = $time ?? strtotime(env('COOKIE_LIFE'));
        $domain = env('COOKIE_DOMAIN') ?? '';
        $secure = !env('DEV');

        setcookie($name, $value, [
            'expires' => $time,
            'path' => '/',
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        self::$COOKIES[$name] = $value;
    }
}
