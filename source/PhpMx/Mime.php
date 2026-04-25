<?php

namespace PhpMx;

/** 
 * Classe utilitária para detecção, tradução e validação de MIME types.
 */
abstract class Mime
{
    protected static array $MIMETYPE = [
        'txt'   => 'text/plain',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'php'   => 'text/x-php',
        'css'   => 'text/css',
        'md'    => 'text/markdown',
        'csv'   => 'text/csv',
        'rtf'   => 'application/rtf',
        'pdf'   => 'application/pdf',

        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'xml'   => 'application/xml',
        'wasm'  => 'application/wasm',
        'yaml'  => 'text/yaml',
        'yml'   => 'text/yaml',
        'map'   => 'application/json',

        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'jpe'   => 'image/jpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'avif'  => 'image/avif',
        'svg'   => 'image/svg+xml',
        'svgz'  => 'image/svg+xml',
        'bmp'   => 'image/bmp',
        'ico'   => 'image/vnd.microsoft.icon',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'psd'   => 'image/vnd.adobe.photoshop',

        'mp3'   => 'audio/mpeg',
        'wav'   => 'audio/wav',
        'mp4'   => 'video/mp4',
        'webm'  => 'video/webm',
        'mov'   => 'video/quicktime',
        'qt'    => 'video/quicktime',
        'flv'   => 'video/x-flv',
        'swf'   => 'application/x-shockwave-flash',

        'doc'   => 'application/msword',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'   => 'application/vnd.ms-excel',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'odt'   => 'application/vnd.oasis.opendocument.text',
        'ods'   => 'application/vnd.oasis.opendocument.spreadsheet',

        'eot'   => 'application/vnd.ms-fontobject',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',

        'zip'   => 'application/zip',
        'rar'   => 'application/x-rar-compressed',
        '7z'    => 'application/x-7z-compressed',
    ];

    /**
     * Retorna a extensão correspondente a um MIME type.
     * @param string $mime O MIME type para busca (ex: 'text/html').
     * @return string|null A extensão sem o ponto (ex: 'html') ou null se não encontrada.
     */
    static function getExMime(string $mime): ?string
    {
        foreach (self::$MIMETYPE as $ex => $item)
            if (strtolower($item) == strtolower($mime))
                return strtolower($ex);

        return null;
    }

    /**
     * Retorna o MIME type correspondente a uma extensão.
     * @param string $ex A extensão para busca (ex: 'jpg').
     * @return string|null O MIME type (ex: 'image/jpeg') ou null se não encontrada.
     */
    static function getMimeEx(string $ex): ?string
    {
        return self::$MIMETYPE[strtolower($ex)] ?? null;
    }

    /**
     * Identifica o MIME type de um arquivo físico baseado em seu conteúdo.
     * @param string $file Caminho para o arquivo.
     * @return string|null O MIME type detectado ou null se o arquivo não existir.
     */
    static function getMimeFile(string $file): ?string
    {
        if (File::check($file))
            return strtolower(mime_content_type(path($file)));

        return null;
    }

    /**
     * Verifica se uma extensão corresponde a um ou mais MIME types ou outras extensões.
     * @param string $ex Extensão base.
     * @param string ...$compare MIME types ou extensões para comparar.
     * @return bool
     */
    static function checkMimeEx(string $ex, string ...$compare): bool
    {
        $mime = self::getMimeEx($ex) ?? '';
        return $mime ? self::checkMimeMime($mime, ...$compare) : false;
    }

    /**
     * Compara um MIME type contra uma lista de outros MIME types ou extensões.
     * @param string $mime MIME type base.
     * @param string ...$compare MIME types ou extensões para comparar.
     * @return bool
     */
    static function checkMimeMime(string $mime, string ...$compare): bool
    {
        foreach ($compare as $item) {
            $item = strpos($item, '/') ? $item : self::getMimeEx($item);
            if (strtolower($item) == strtolower($mime))
                return true;
        }

        return false;
    }

    /**
     * Verifica se o MIME type real de um arquivo corresponde aos tipos fornecidos.
     * @param string $file Caminho para o arquivo.
     * @param string ...$compare MIME types ou extensões para comparar.
     * @return bool
     */
    static function checkMimeFile(string $file, string ...$compare): bool
    {
        $mime = self::getMimeFile($file) ?? '';
        return $mime ? self::checkMimeMime($mime, ...$compare) : false;
    }
}
