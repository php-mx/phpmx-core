<?php

namespace PhpMx\ViewRender;

use PhpMx\Code;
use PhpMx\View;

abstract class ViewRenderVue extends View
{
    protected static array $IMPORTED_HASH = [];

    protected static array $PREPARE_REPLACE = [
        '// [#' => '[#',
        '/* [#' => '[#',
        '] */' => ']',
        '<!-- [#' => '[#',
        '] -->' => ']',
        '<!--[#' => '[#',
        ']-->' => ']',
    ];

    /** Aplica ações extras ao renderizar uma view */
    protected static function renderizeAction(string $content, $params = []): string
    {
        if (!isset($params['name'])) return '';
        $name = $params['name'];

        $content = str_replace(array_keys(self::$PREPARE_REPLACE),  array_values(self::$PREPARE_REPLACE),  $content);

        $hash = Code::on([$content, self::__currentGet('data'), self::$SCOPE]);

        if (isset(self::$IMPORTED_HASH[$hash])) return '';

        self::$IMPORTED_HASH[$hash] = true;

        $content = self::applyPrepare($content);

        list($template, $script, $style) = self::explodeVue($content);

        $template = trim($template);
        $template = str_replace_all(["\n", "  "], [' '], $template);
        $template = base64_encode($template);
        $template = "\nvueApp.template = atob('$template')";

        $script = str_replace('export default', "const vueApp = ", $script);

        $style = "<style>$style</style>";

        $content = "()=>{\n$script\n$template\nreturn vueApp;}";

        if (!self::parentType('vue')) {
            $content = "<script>\nenergize.vue($content,'$name');\n</script>$style";
        } else {
            $content = "$name: ($content)(),$style";
        }

        return $content;
    }

    protected static function explodeVue($content): array
    {
        $template = '';
        $script = '';
        $style = '';

        preg_match_all('/<template[^>]*>(.*?)<\/template>/is', $content, $matches);
        foreach ($matches[1] as $i => $inner) {
            $template .= "\n" . trim($inner);
            $content = str_replace($matches[0][$i], '', $content);
        }

        preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $content, $matches);
        foreach ($matches[1] as $i => $inner) {
            $style .= "\n" . trim($inner);
            $content = str_replace($matches[0][$i], '', $content);
        }

        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $content, $matches);
        foreach ($matches[1] as $i => $inner) {
            $script .= "\n" . trim($inner);
            $content = str_replace($matches[0][$i], '', $content);
        }

        return [$template, $script, $style];
    }
}
