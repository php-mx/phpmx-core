<?php

if (!function_exists('str_get_var')) {

    /**
     * Extrai e converte um valor de dentro de uma string para seu tipo real (bool, int, float ou null).
     * Útil para processar valores vindos de arquivos .env ou inputs de texto.
     * @param mixed $var
     * @return mixed
     */
    function str_get_var($var): mixed
    {
        if (!is_string($var))
            return $var;

        if ($var === 'null' || $var === 'NULL' || $var === '')
            return null;

        if ($var == 'true' || $var === 'TRUE')
            return true;

        if ($var === 'false' || $var === 'FALSE')
            return false;

        if (strval(intval($var)) === $var)
            return intval($var);

        if (strval(floatval($var)) === $var)
            return floatval($var);

        return $var;
    }
}

if (!function_exists('str_replace_all')) {

    /**
     * Substitui repetidamente as ocorrências de uma string até que não haja mais mudanças ou atinja o limite.
     * @param array|string $search
     * @param array|string $replace
     * @param string $subject
     * @param int $loop Limite de iterações para evitar loops infinitos.
     * @return string
     */
    function str_replace_all(array|string $search, array|string $replace, string $subject, int $loop = 10): string
    {
        $count = 0;
        $subject = str_replace($search, $replace, $subject, $count);
        while ($loop && $count) {
            $subject = str_replace($search, $replace, $subject, $count);
            $loop--;
        }
        return $subject;
    }
}

if (!function_exists('str_replace_first')) {

    /**
     * Substitui apenas a primeira ocorrência encontrada da string de pesquisa.
     * @param array|string $search
     * @param array|string $replace
     * @param string $subject
     * @return string
     */
    function str_replace_first(array|string $search, array|string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }
}

if (!function_exists('str_replace_last')) {

    /**
     * Substitui apenas a última ocorrência encontrada da string de pesquisa.
     * @param array|string $search
     * @param array|string $replace
     * @param string $subject
     * @return string
     */
    function str_replace_last(array|string $search, array|string $replace, string $subject): string
    {
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }
}

if (!function_exists('str_trim')) {

    /**
     * Remove espaços ou caracteres específicos do entorno de uma substring dentro de uma string maior.
     * @param string $string O texto completo.
     * @param array|string $substring A parte que deve ser limpa.
     * @param array|string $characters Caracteres a serem removidos.
     * @return string
     */
    function str_trim(string $string, array|string $substring, array|string $characters = " \t\n\r\0\x0B"): string
    {
        $charactersArray = [];
        $substringArray = [];

        $characters = is_array($characters) ? $characters : [$characters];
        $substring = is_array($substring) ? $substring : [$substring];

        foreach ($substring as $vs)
            foreach ($characters as $vt) {
                $charactersArray[] = "$vs$vt";
                $charactersArray[] = "$vt$vs";
                $substringArray[] = $vs;
                $substringArray[] = $vs;
            }

        $string = mb_str_replace_all($charactersArray, $substringArray, $string);

        return $string;
    }
}

if (!function_exists('mb_str_replace')) {

    /**
     * Versão multibyte segura da função str_replace.
     * @param array|string $search
     * @param array|string $replace
     * @param string $subject
     * @param int &$count Referência para contagem de substituições.
     * @return string
     */
    function mb_str_replace(array|string $search, array|string $replace, string $subject, &$count = 0): string
    {
        if (!is_array($subject)) {
            $searches = is_array($search) ? array_values($search) : array($search);
            $replacements = is_array($replace) ? array_values($replace) : array($replace);
            $replacements = array_pad($replacements, count($searches), '');
            foreach ($searches as $key => $search) {
                $parts = mb_split(preg_quote($search), $subject);
                $count += count($parts) - 1;
                $subject = implode($replacements[$key], $parts);
            }
        } else {
            foreach ($subject as $key => $value)
                $subject[$key] = mb_str_replace($search, $replace, $value, $count);
        }
        return $subject;
    }
}

if (!function_exists('mb_str_replace_all')) {

    /**
     * Versão multibyte segura da função str_replace_all.
     * @param array|string $search
     * @param array|string $replace
     * @param string $subject
     * @param int $loop
     * @return string
     */
    function mb_str_replace_all(array|string $search, array|string $replace, string $subject, int $loop = 10): string
    {
        $pre = $subject;
        $subject = mb_str_replace($search, $replace, $subject);
        while ($loop && $pre != $subject) {
            $pre = $subject;
            $subject = mb_str_replace($search, $replace, $subject);
            $loop--;
        }
        return $subject;
    }
}
