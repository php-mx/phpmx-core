<?php

namespace PhpMx;

/**
 * Classe utilitária para manipulação de arquivos.
 */
abstract class File
{
    /**
     * Cria um arquivo de texto.
     * @param string $path Caminho do arquivo.
     * @param string $content Conteúdo a ser gravado.
     * @param bool $recreate Se deve sobrescrever o arquivo caso ele já exista.
     * @return bool|null True se criado, null se já existia e não foi recriado.
     */
    static function create(string $path, string $content, bool $recreate = false): ?bool
    {
        $path = path($path);

        return Log::add('file', "create $path", function () use ($path, $content, $recreate) {
            if ($recreate || !self::check($path)) {
                $path = path($path);
                if (File::getOnly($path) != Dir::getOnly($path))
                    Dir::create($path);
                $fp = fopen($path, 'w');
                fwrite($fp, $content);
                fclose($fp);
                return true;
            }
            return null;
        });
    }

    /**
     * Remove um arquivo físico.
     * @param string $path Caminho do arquivo.
     * @return bool|null True se removido com sucesso, null se o arquivo não existir.
     */
    static function remove(string $path): ?bool
    {
        $path = path($path);

        return Log::add('file', "remove $path", function () use ($path) {
            if (self::check($path)) {
                $path = path($path);
                unlink($path);
                return !is_file($path);
            }
            return null;
        });
    }

    /**
     * Cria uma cópia de um arquivo.
     * @param string $path_from Caminho de origem.
     * @param string $path_to Caminho de destino.
     * @param bool $replace Se deve substituir o arquivo de destino caso ele já exista.
     * @return bool|null True se copiado, null se o destino já existe e não foi substituído.
     */
    static function copy(string $path_from, string $path_to, bool $replace = false): ?bool
    {
        $path_from = path($path_from);
        $path_to = path($path_to);

        return Log::add('file', "copy $path_from to $path_to", function () use ($path_from, $path_to, $replace) {
            if ($replace || !self::check($path_to)) {
                if (self::check($path_from)) {
                    Dir::create($path_to);
                    return boolval(copy(path($path_from), path($path_to)));
                }
            }
            return null;
        });
    }

    /**
     * Altera o local ou o nome de um arquivo (move/rename).
     * @param string $path_from Caminho de origem.
     * @param string $path_to Caminho de destino.
     * @param bool $replace Se deve substituir o arquivo de destino caso ele já exista.
     * @return bool|null True se movido, null se o destino já existe e não foi substituído.
     */
    static function move(string $path_from, string $path_to, bool $replace = false): ?bool
    {
        $path_from = path($path_from);
        $path_to = path($path_to);

        return Log::add('file', "move $path_from to $path_to", function () use ($path_from, $path_to, $replace) {
            if ($replace || !self::check($path_to)) {
                if (self::check($path_from)) {
                    Dir::create($path_to);
                    return boolval(rename(path($path_from), path($path_to)));
                }
            }
            return null;
        });
    }

    /**
     * Retorna apenas o nome do arquivo com a sua respectiva extensão.
     * @param string $path Caminho do arquivo.
     * @return string
     */
    static function getOnly(string $path): string
    {
        $path = path($path);
        $path = explode('/', $path);
        return array_pop($path);
    }

    /**
     * Retorna apenas o nome do arquivo, removendo a extensão.
     * @param string $path Caminho do arquivo.
     * @return string
     */
    static function getName(string $path): string
    {
        $fileName = self::getOnly($path);
        $ex = self::getEx($path);
        $ex = substr($fileName, 0, (strlen($ex) + 1) * -1);
        return $ex;
    }

    /**
     * Retorna apenas a extensão do arquivo em letras minúsculas.
     * @param string $path Caminho do arquivo.
     * @return string
     */
    static function getEx(string $path): string
    {
        $parts = explode('.', self::getOnly($path));
        return strtolower(end($parts));
    }

    /**
     * Define ou altera a extensão de um caminho de arquivo.
     * @param string $path Caminho original.
     * @param string $extension Nova extensão (padrão 'php').
     * @return string Caminho atualizado.
     */
    static function setEx(string $path, string $extension = 'php'): string
    {
        $extension = trim($extension, '.');
        if (!str_ends_with($path, ".$extension")) {
            $path = explode('.', $path);
            if (count($path) > 1) array_pop($path);
            $path[] = $extension;
            $path = implode('.', $path);
        }
        return $path;
    }

    /**
     * Verifica se um arquivo existe no caminho especificado.
     * @param string $path Caminho do arquivo.
     * @return bool
     */
    static function check(string $path): bool
    {
        return is_file(path($path));
    }

    /**
     * Retorna o tamanho do arquivo em bytes ou formato legível (human-readable).
     * @param string $path Caminho do arquivo.
     * @param bool $human Se true, retorna formatado (ex: '10 kb'). Se false, retorna bytes.
     * @return int|string
     */
    static function getSize($path, $human = true): int|string
    {
        $path = path($path);
        if (!self::check($path)) return '-';
        $size = filesize($path);
        if ($human) {
            $units = [' b', ' kb', ' mb', ' gb', ' tb'];
            $i = 0;
            while ($size >= 1024 && $i < count($units) - 1) {
                $size /= 1024;
                $i++;
            }
            $size = round($size, 2) . $units[$i];
        }
        return $size;
    }

    /**
     * Retorna o timestamp da última modificação do arquivo.
     * @param string $path Caminho do arquivo.
     * @return int|null Timestamp ou null se o arquivo não existir.
     */
    static function getLastModified($path): ?int
    {
        $path = path($path);
        $lastModified = self::check($path) ? filemtime($path) : null;
        return $lastModified;
    }
}
