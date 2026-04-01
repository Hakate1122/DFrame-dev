<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - Pancake Sort**
 * 
 * Implements the Pancake Sort algorithm to sort an array.
 * 
 * **Principle**: Pancake Sort is a sorting algorithm that sorts an array by repeatedly flipping subarrays. The algorithm works by finding the maximum element in the unsorted portion of the array and flipping it to the front, then flipping the entire unsorted portion to move the maximum element to its correct position at the end of the array. This process is repeated until the entire array is sorted.
 * 
 * **Complexity**: O(n^2) time complexity in the worst and average cases, and O(n) in the best case (when the array is already sorted). The space complexity is O(1) since it is an in-place sorting algorithm.
 */

class PancakeSort{
    public static function sort(array $arr): array
    {
        $n = count($arr);

        for ($curr_size = $n; $curr_size > 1; $curr_size--) {
            // Find the index of the maximum element in arr[0..curr_size-1]
            $max_index = self::findMax($arr, $curr_size);

            // Move the maximum element to end of current array if it's not
            // already at the end
            if ($max_index != $curr_size - 1) {
                // To move at the end, first move maximum number to beginning
                self::flip($arr, $max_index);

                // Now move the maximum number to end by reversing current array
                self::flip($arr, $curr_size - 1);
            }
        }

        return $arr;
    }

    private static function flip(array &$arr, int $i): void
    {
        $start = 0;
        while ($start < $i) {
            // Swap arr[start] and arr[i]
            [$arr[$start], $arr[$i]] = [$arr[$i], $arr[$start]];
            $start++;
            $i--;
        }
    }

    private static function findMax(array $arr, int $n): int
    {
        $max_index = 0;
        for ($i = 1; $i < $n; ++$i) {
            if ($arr[$i] > $arr[$max_index]) {
                $max_index = $i;
            }
        }
        return $max_index;
    }
}