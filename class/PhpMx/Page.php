<?php

namespace PhpMx;

use PhpMx\Code;

abstract class Page
{
    protected static array $HEAD = [];
    protected static array $ALERT = [];
    protected static ?string $LAYOUT = null;
    protected static ?string $LAYOUT_STATE = null;

    /** Define o layout que deve ser utilizado */
    static function layout(?string $layout): void
    {
        self::$LAYOUT = $layout;
        self::layoutState(self::$LAYOUT_STATE);
    }

    /** Define o estado do layout */
    static function layoutState(?string $layoutState): void
    {
        self::$LAYOUT_STATE = Code::on([self::$LAYOUT ?? uuid(), $layoutState]);
    }

    /** Define um valor para uma subpropriedade da tag [#HEAD] */
    static function head(string $name, mixed $value): void
    {
        self::$HEAD[$name] = $value;
    }

    /** Define o valor para a propriedade [#HEAD.title] */
    static function title(string $title): void
    {
        self::head('title', $title);
    }

    /** Define o valor para a propriedade [#HEAD.favicon] */
    static function favicon(string $favicon): void
    {
        self::head('favicon', $favicon);
    }

    /** Define o valor para a propriedade [#HEAD.description] */
    static function description(string $description): void
    {
        self::head('description', $description);
    }

    /** Adiciona um alerta para o frontend */
    static function alert(string $title, string|bool|null $content = null, ?bool $type = null): void
    {
        if (!is_string($content)) {
            $type = $content;
            $content = '';
        }

        $type = match ($type) {
            true => 'success',
            false => 'error',
            default => 'neutral'
        };

        self::$ALERT[] = [$title, $content, $type];
    }
}
