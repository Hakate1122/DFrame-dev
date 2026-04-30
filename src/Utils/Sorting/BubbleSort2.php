<?php

declare(strict_types=1);

namespace DFrame\Utils\Sorting;

use function sizeof;

/**
 * **Utility: Sorting - BubbleSort2**
 * 
 * Implements the Bubble Sort algorithm to sort an array.
 * **Principle**: Repeatedly step through the list, compare adjacent elements and swap them if they are in the wrong order. The process is repeated until the list is sorted.
 * 
 * **Complexity**: O(n^2) time complexity in the worst and average cases, O(n) in the best case (when the array is already sorted), and O(1) space complexity.
 */
class BubbleSort2
{
    /**
     * Sorts an array using the Bubble Sort algorithm.
     * @param array $input The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $input)
    {
        if (!isset($input)) {
            return [];
        }

        do {
            $swapped = false;

            for ($i = 0, $count = count($input) - 1; $i < $count; $i++) {
                if ($input[$i + 1] < $input[$i]) {
                    [$input[$i + 1], $input[$i]] = [$input[$i], $input[$i + 1]];
                    $swapped = true;
                }
            }
        } while ($swapped);
        return $input;
    }

}
