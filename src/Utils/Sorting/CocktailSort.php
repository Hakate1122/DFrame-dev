<?php

namespace DFrame\Utils\Sorting;

use function count;

/**
 * **Utility: Sorting - Cocktail Sort**
 * 
 * Implements the Cocktail Sort algorithm to sort an array.
 * 
 * **Principle**: Cocktail Sort, also known as Bidirectional Bubble Sort, is a variation of the Bubble Sort algorithm that sorts in both directions on each pass through the list. It works by first traversing the list from left to right, comparing adjacent elements and swapping them if they are in the wrong order. After reaching the end of the list, it traverses back from right to left, performing the same comparisons and swaps. This process is repeated until no swaps are needed, indicating that the list is sorted.
 * 
 * **Complexity**: O(n^2) time complexity in the worst and average cases, O(n) in the best case (when the array is already sorted), and O(1) space complexity since it is an in-place sorting algorithm.
 */

class CocktailSort{
    public static function sort(array $arr): array
    {
        $n = count($arr);
        $swapped = true;
        $start = 0;
        $end = $n - 1;

        while ($swapped) {
            $swapped = false;

            for ($i = $start; $i < $end; ++$i) {
                if ($arr[$i] > $arr[$i + 1]) {
                    [$arr[$i], $arr[$i + 1]] = [$arr[$i + 1], $arr[$i]];
                    $swapped = true;
                }
            }

            if (!$swapped) {
                break;
            }

            $swapped = false;

            --$end;

            for ($i = $end; $i >= $start; --$i) {
                if ($arr[$i] > $arr[$i + 1]) {
                    [$arr[$i], $arr[$i + 1]] = [$arr[$i + 1], $arr[$i]];
                    $swapped = true;
                }
            }

            ++$start;
        }

        return $arr;
    }
}