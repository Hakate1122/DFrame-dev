<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - CountSort**
 * 
 * Implements the Counting Sort algorithm to sort an array.
 */
class CountSort
{
    /**
     * Sorts an array using the Counting Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $array): array
    {
        $count = array();
        $min = min($array);
        $max = max($array);

        for ($i = $min; $i <= $max; $i++) {
            $count[$i] = 0;
        }

        foreach ($array as $number) {
            $count[$number]++;
        }

        $z = 0;

        for ($i = $min; $i <= $max; $i++) {
            while ($count[$i]-- > 0) {
                $array[$z++] = $i;
            }
        }

        return $array;
    }
}
