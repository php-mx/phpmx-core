<?php

namespace PhpMx;

/**
 * Classe utilitária para gerenciamento, normalização e busca de caminhos.
 * Centraliza a resolução de arquivos e diretórios dentro da estrutura do framework.
 */
abstract class Path
{
    /** @ignore */
    static protected array $paths = [];

    /**
     * Identifica o pacote de origem de um diretório ou arquivo.
     * @param string $path Caminho para análise.
     * @return string Nome do pacote (vendor-pacote) ou 'current-project'.
     */
    static function origin($path): string
    {
        $path = path($path);
        if (str_starts_with($path, 'vendor/')) {
            $path = strtolower($path);
            $path = explode('/', $path);
            return $path[1] . '-' . $path[2];
        }
        return 'current-project';
    }

    /**
     * Formata e normaliza um ou mais segmentos de caminho.
     * Remove redundâncias, converte barras invertidas e limpa o caminho relativo ao root.
     * @param string ...$segments Segmentos do caminho (aceita múltiplos argumentos).
     * @return string Caminho formatado e normalizado.
     */
    static function format(): string
    {
        $path = array_values(func_get_args());
        $path = implode('/', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace_all('//', '/', $path);

        $currentPath = getcwd();
        $currentPath = str_replace('\\', '/', $currentPath);
        $currentPath = rtrim($currentPath, '/');

        if (str_starts_with($path, $currentPath))
            $path = substr($path, strlen($currentPath));

        $path = ltrim($path, '/');

        if (str_starts_with($path, './'))
            $path = substr($path, 2);

        $path = str_trim($path, '/', '/ ');
        $path = str_replace_all('//', '/', $path);

        return $path;
    }

    /**
     * Registra um novo diretório na pilha de busca para importação de arquivos.
     * @param string $path Diretório a ser registrado.
     * @return void
     */
    static function register($path): void
    {
        self::$paths[] = self::format($path);
    }

    /**
     * Retorna a lista de caminhos registrados para busca, em ordem inversa (prioridade do último registrado).
     * @return array
     */
    static function registred(): array
    {
        return array_reverse(self::$paths);
    }

    /**
     * Busca o primeiro arquivo existente percorrendo os caminhos registrados.
     * @param string ...$args Segmentos do nome/caminho do arquivo.
     * @return string|null Caminho completo do arquivo encontrado ou null.
     */
    static function seekForFile(): ?string
    {
        $path = self::format(...func_get_args());

        foreach (self::registred() as $registred)
            if (File::check("$registred/$path"))
                return self::format("$registred/$path");

        return null;
    }

    /**
     * Busca e retorna todos os arquivos correspondentes encontrados nos caminhos registrados.
     * @param string ...$args Segmentos do nome/caminho do arquivo.
     * @return array Lista de caminhos encontrados (sem duplicatas).
     */
    static function seekForFiles(): array
    {
        $path = self::format(...func_get_args());
        $result = [];

        foreach (self::registred() as $registred)
            if (File::check("$registred/$path")) {
                $file = self::format("$registred/$path");
                $result[md5($file)] = $file;
            }

        return array_values($result);
    }

    /**
     * Busca o primeiro diretório existente percorrendo os caminhos registrados.
     * @param string ...$args Segmentos do nome/caminho do diretório.
     * @return string|null Caminho completo do diretório encontrado ou null.
     */
    static function seekForDir(): ?string
    {
        $path = self::format(...func_get_args());

        foreach (self::registred() as $registred)
            if (Dir::check("$registred/$path"))
                return self::format("$registred/$path");

        return null;
    }

    /**
     * Busca e retorna todos os diretórios correspondentes encontrados nos caminhos registrados.
     * @param string ...$args Segmentos do nome/caminho do diretório.
     * @return array Lista de caminhos encontrados.
     */
    static function seekForDirs(): array
    {
        $path = self::format(...func_get_args());
        $result = [];

        foreach (self::registred() as $registred)
            if (Dir::check("$registred/$path")) {
                $dir = self::format("$registred/$path");
                $result[md5($dir)] = $dir;
            }

        return array_values($result);
    }
}
