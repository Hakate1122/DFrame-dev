<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - SelectionSort**
 * 
 * Implements the Selection Sort algorithm to sort an array.
 */
class SelectionSort
{
    /**
     * Sorts an array using the Selection Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $array): array
    {
        $length = count($array);

        for ($i = 0; $i < $length; $i++) {
            $lowest = $i;

            for ($j = $i + 1; $j < $length; $j++) {
                if ($array[$j] < $array[$lowest]) {
                    $lowest = $j;
                }
            }

            if ($i !== $lowest) {
                $temp = $array[$i];
                $array[$i] = $array[$lowest];
                $array[$lowest] = $temp;
            }
        }

        return $array;
    }
}
