<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - QuickSort**
 * 
 * Implements the Quick Sort algorithm to sort an array.
 * Compare number in an array to the next number and sets to new array (greater than or less than)
 */
class QuickSort
{
    /**
     * Sorts an array using the Quick Sort algorithm.
     * @param array $input The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $input): array
    {
        // Return nothing if input is empty
        if (empty($input)) {
            return [];
        }

        $lt = [];
        $gt = [];
        if (sizeof($input) < 2) {
            return $input;
        }

        $key = key($input);
        $shift = array_shift($input);
        foreach ($input as $value) {
            $value <= $shift ? $lt[] = $value : $gt[] = $value;
        }

        return array_merge(self::sort($lt), [$key => $shift], self::sort($gt));
    }
}
