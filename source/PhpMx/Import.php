<?php

namespace PhpMx;

/**
 * Classe utilitária para importar arquivos e extrair valores.
 * Gerencia a inclusão de scripts PHP e a captura de conteúdos/variáveis com isolamento de escopo.
 */
abstract class Import
{
    /**
     * Importa um arquivo PHP.
     * @param string $filePath Caminho do arquivo.
     * @param bool $once Define se deve usar require_once ou require.
     * @return bool
     */
    static function only(string $filePath, bool $once = true): bool
    {
        $filePath = path($filePath);
        $filePath = File::setEx($filePath, 'php');

        return Log::add('import', "only $filePath", function () use ($filePath, $once) {
            if (File::check($filePath))
                return $once ? require_once $filePath : require $filePath;
            return false;
        });
    }

    /**
     * Retorna o conteúdo de um arquivo com suporte a processamento de template.
     * @param string $filePath Caminho do arquivo.
     * @param string|array $prepare Dados para substituição via prepare.
     * @return string
     */
    static function content(string $filePath, string|array $prepare = []): string
    {
        $filePath = path($filePath);

        return Log::add('import', "content $filePath", function () use ($filePath, $prepare) {
            $content = File::check($filePath) ? file_get_contents($filePath) : '';
            $return = prepare($content, $prepare);
            return $return;
        });
    }

    /**
     * Retorna o valor retornado (return) por um arquivo PHP.
     * @param string $filePath Caminho do arquivo PHP.
     * @param array $params Variáveis a serem extraídas para o escopo do arquivo.
     * @return mixed
     */
    static function return(string $filePath, array $params = []): mixed
    {
        $filePath = path($filePath);
        $filePath = File::setEx($filePath, 'php');

        return Log::add('import', "return $filePath", function () use ($filePath, $params) {
            if (File::check($filePath)) {
                $return = function ($__FILEPATH__, &$__PARAMS__) {
                    foreach (array_keys($__PARAMS__) as $__KEY__)
                        if (!is_numeric($__KEY__))
                            $$__KEY__ = &$__PARAMS__[$__KEY__];
                    ob_start();
                    $__RETURN__ = require $__FILEPATH__;
                    ob_end_clean();
                    return $__RETURN__;
                };

                $return = $return($filePath, $params);
                return $return ?? null;
            }
        });
    }

    /**
     * Retorna o valor de uma variável específica definida dentro de um arquivo PHP.
     * @param string $filePath Caminho do arquivo PHP.
     * @param string $varName Nome da variável a ser extraída.
     * @param array $params Variáveis de contexto para o arquivo.
     * @return mixed
     */
    static function var(string $filePath, string $varName, array $params = []): mixed
    {
        $filePath = path($filePath);
        $filePath = File::setEx($filePath, 'php');

        return Log::add('import', "variable $varName in $filePath", function () use ($filePath, $varName, $params) {
            if (File::check($filePath)) {
                $return = function ($__FILEPATH__, $__VARNAME__, &$__PARAMS__) {
                    foreach (array_keys($__PARAMS__) as $__KEY__)
                        if (!is_numeric($__KEY__))
                            $$__KEY__ = &$__PARAMS__[$__KEY__];
                    ob_start();
                    require $__FILEPATH__;
                    $__RETURN__ = $$__VARNAME__ ?? null;
                    ob_end_clean();
                    return $__RETURN__;
                };

                $return = $return($filePath, $varName, $params);
            }

            return $return ?? null;
        });
    }

    /**
     * Retorna a saída de texto (buffer) gerada pela execução de um arquivo.
     * @param string $filePath Caminho do arquivo.
     * @param array $params Variáveis de contexto para o arquivo.
     * @return string
     */
    static function output(string $filePath, array $params = []): string
    {
        $filePath = path($filePath);

        return Log::add('import', "output $filePath", function () use ($filePath,  $params) {
            if (File::check($filePath)) {
                $return = function ($__FILEPATH__, &$__PARAMS__) {
                    foreach (array_keys($__PARAMS__) as $__KEY__)
                        if (!is_numeric($__KEY__))
                            $$__KEY__ = &$__PARAMS__[$__KEY__];
                    ob_start();
                    require $__FILEPATH__;
                    $__RETURN__ = ob_get_clean();
                    return $__RETURN__;
                };
                $return = $return($filePath, $params);
            }

            return $return ?? '';
        });
    }
}
