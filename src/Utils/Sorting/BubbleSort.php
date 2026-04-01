<?php

namespace DFrame\Utils\Sorting;

use function count;

/**
 * **Utility: Sorting - BubbleSort**
 * 
 * Implements the Bubble Sort algorithm to sort an array.
 * **Principle**: Repeatedly step through the list, compare adjacent elements and swap them if they are in the wrong order. The process is repeated until the list is sorted.
 * 
 * **Complexity**: O(n^2) time complexity in the worst and average cases, O(n) in the best case (when the array is already sorted), and O(1) space complexity.
 */
class BubbleSort
{
    /**
     * Sorts an array using the Bubble Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort($array)
    {
        $length = count($array);

        for ($i = $length; $i > 0; $i--) {
            $swapped = true;

            for ($j = 0; $j < $i - 1; $j++) {
                if ($array[$j] > $array[$j + 1]) {
                    $temp = $array[$j];
                    $array[$j] = $array[$j + 1];
                    $array[$j + 1] = $temp;
                    $swapped = false;
                }
            }

            if ($swapped) {
                break;
            }
        }

        return $array;
    }
}
