<?php

namespace DFrame\Utils\Sorting;
use function count;

/**
* **Utility: Sorting - Odd-Even Sort**

 * Implements the Odd-Even Sort algorithm to sort an array.
 * 
 * **Principle**: Odd-Even Sort is a simple comparison-based sorting algorithm that works by repeatedly performing two passes through the list: one for odd indexed elements and one for even indexed elements. During each pass, adjacent elements are compared and swapped if they are in the wrong order. This process is repeated until the list is sorted.
 * 
 * **Complexity**: O(n^2) time complexity in the worst and average cases, O(n) in the best case (when the array is already sorted), and O(1) space complexity since it is an in-place sorting algorithm.
 */
class OddEvenSort
{
    public static function sort(array $arr): array
    {
        $n = count($arr);
        $sorted = false;

        while (!$sorted) {
            $sorted = true;

            // Perform odd indexed passes
            for ($i = 1; $i < $n - 1; $i += 2) {
                if ($arr[$i] > $arr[$i + 1]) {
                    // Swap
                    $temp = $arr[$i];
                    $arr[$i] = $arr[$i + 1];
                    $arr[$i + 1] = $temp;
                    $sorted = false;
                }
            }

            // Perform even indexed passes
            for ($i = 0; $i < $n - 1; $i += 2) {
                if ($arr[$i] > $arr[$i + 1]) {
                    // Swap
                    $temp = $arr[$i];
                    $arr[$i] = $arr[$i + 1];
                    $arr[$i + 1] = $temp;
                    $sorted = false;
                }
            }
        }

        return $arr;
    }
}