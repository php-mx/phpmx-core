<?php

if (!function_exists('num_format')) {

    /**
     * Formata um número para o tipo float com controle de casas decimais e tipo de arredondamento.
     * @param int|float|string $number O número a ser formatado.
     * @param int $decimals Quantidade de casas decimais.
     * @param int $roundType Tipo de arredondamento (-1: baixo, 0: comum, 1: cima).
     * @return float
     */
    function num_format(int|float|string $number, int $decimals = 2, int $roundType = -1): float
    {
        $decimals++;
        $decimals = max(0, $decimals);

        if (!$decimals)
            return num_round($number, $roundType);

        $number = str_replace(',', '.', $number);

        $tmp = explode('.', $number);
        $n_int = array_shift($tmp);
        $n_decimal = substr(array_shift($tmp) ?? '0', 0, $decimals + 1);

        if (strlen($n_decimal) > $decimals)
            $n_decimal = num_round(($n_decimal / 10), $roundType);

        return "$n_int.$n_decimal";
    }
}

if (!function_exists('num_round')) {

    /**
     * Arredonda um número de acordo com o tipo especificado.
     * @param int|float|string $number O número para arredondar.
     * @param int $roundType -1 para baixo (floor), 0 para comum (round), 1 para cima (ceil).
     * @return int
     */
    function num_round(int|float|string $number, int $roundType = 0): int
    {
        $number = str_replace(',', '.', $number);

        $number = match ($roundType) {
            -1 => floor($number),
            0 => round($number),
            1 => ceil($number),
            default => explode('.', $number)[0],
        };

        return $number;
    }
}

if (!function_exists('num_interval')) {

    /**
     * Garante que um número esteja contido dentro de um intervalo mínimo e máximo.
     * @param int|float|string $number O valor base.
     * @param int|float|string $min Valor mínimo permitido.
     * @param int|float|string $max Valor máximo permitido.
     * @return int|float
     */
    function num_interval(int|float|string $number, int|float|string $min = 0, int|float|string $max = 0): int|float
    {
        $number = str_replace(',', '.', $number);
        $min = str_replace(',', '.', $min);
        $max = str_replace(',', '.', $max);

        $min = $min ?? $number;
        $max = $max ?? $number;

        return min(max($min, $number), $max);
    }
}

if (!function_exists('num_positive')) {

    /**
     * Retorna o valor absoluto (positivo) de um número.
     * @param int|float|string $number
     * @return int|float
     */
    function num_positive(int|float|string $number): int|float
    {
        $number = str_replace(',', '.', $number);
        return max($number, ($number * -1));
    }
}

if (!function_exists('num_negative')) {

    /**
     * Retorna a representação negativa de um número.
     * @param int|float|string $number
     * @return int|float
     */
    function num_negative(int|float|string $number): int|float
    {
        return num_positive($number) * -1;
    }
}
