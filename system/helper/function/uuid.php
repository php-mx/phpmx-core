<?php

if (!function_exists('uuid')) {

    /**
     * Gera uma string de identificação única curta e personalizada.
     * Combina 12 caracteres aleatórios com o timestamp atual convertido para base 36.
     * @return string ID gerado (ex: _aB1c2D3e4F5g6h7i8j).
     */
    function uuid(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $id = '_';
        for ($i = 0; $i < 12; $i++)
            $id .= $characters[random_int(0, strlen($characters) - 1)];
        $id .= base_convert(time(), 10, 36);
        return $id;
    }
}
