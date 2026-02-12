<?php

if (!function_exists('applyChanges')) {

    /**
     * Aplica mudanças em um array de forma recursiva. 
     * Valores nulos nas mudanças resultam na remoção da chave correspondente.
     * @param array &$array Array original que receberá as alterações (passado por referência).
     * @param array $changes Mapa de alterações a serem aplicadas.
     * @return void
     */
    function applyChanges(&$array, $changes): void
    {
        foreach ($changes as $key => $newValue) {
            if (isset($array[$key])) {
                if (is_null($newValue)) {
                    unset($array[$key]);
                } elseif (is_array($newValue) && is_array($array[$key])) {
                    applyChanges($array[$key], $newValue);
                } else {
                    $array[$key] = $newValue;
                }
            } elseif (!is_null($newValue)) {
                $array[$key] = $newValue;
            }
        }
    }
}

if (!function_exists('getChanges')) {

    /**
     * Compara dois arrays e retorna as diferenças estruturais.
     * Chaves presentes no original mas ausentes no alterado são marcadas como null.
     * @param array $changed Array com as versões novas dos dados.
     * @param array $original Array com os dados originais.
     * @return array Mapa de mudanças encontradas.
     */
    function getChanges($changed, $original): array
    {
        $changes = [];
        foreach ($changed as $key => $newValue) {
            if (isset($original[$key])) {
                if (is_array($newValue) && is_array($original[$key])) {
                    $innerChanges = getChanges($newValue, $original[$key]);
                    if (count($innerChanges)) {
                        $changes[$key] = $innerChanges;
                    }
                } elseif ($newValue !== $original[$key]) {
                    $changes[$key] = $newValue;
                }
            } else {
                $changes[$key] = $newValue;
            }
        }

        foreach ($original as $key => $value) {
            if (!isset($changed[$key])) {
                $changes[$key] = null;
            }
        }

        return $changes;
    }
}
