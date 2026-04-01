<?php

namespace DFrame\Utils\Sorting;

use function count;

/**
 * **Utility: Sorting - SelectionSort**
 * 
 * Implements the Selection Sort algorithm to sort an array.
 * **Principle**: Repeatedly select the smallest unsorted element and swap it with the first unsorted element until the entire array is sorted.
 * 
 * **Complexity**: O(n^2) time complexity in all cases (best, average, worst) and O(1) space complexity (in-place sorting).
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
