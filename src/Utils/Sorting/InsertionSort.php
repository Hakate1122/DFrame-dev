<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - InsertionSort**
 * 
 * Implements the Insertion Sort algorithm to sort an array.
 * **Principle**: Builds the sorted array one item at a time by repeatedly taking the next item and inserting it into the correct position in the already sorted part of the array.
 * 
 * **Complexity**: O(n^2) time complexity in the worst and average cases, O(n) in the best case (when the array is already sorted), and O(1) space complexity.
 */
class InsertionSort
{
    /**
     * Sorts an array using the Insertion Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $array): array
    {
        for ($i = 1; $i < count($array); $i++) {
            $currentVal = $array[$i];

            for ($j = $i - 1; $j >= 0 && $array[$j] > $currentVal; $j--) {
                $array[$j + 1] = $array[$j];
            }

            $array[$j + 1] = $currentVal;
        }

        return $array;
    }

}
