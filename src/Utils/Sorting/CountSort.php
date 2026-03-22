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

    /**
     * Debug version of counting sort that reports the count array after counting occurrences.
     *
     * @param array $array The array to sort.
     * @param callable|null $onCount Optional callback invoked after counting occurrences: function(array $count, int $step).
     *                             If null, the method will echo the count array as JSON.
     * @return array The sorted array.
     */
    public static function debug(array $array, ?callable $onCount = null)
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

        if ($onCount !== null) {
            $onCount($count, 1);
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