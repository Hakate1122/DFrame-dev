<?php

namespace DFrame\Utils\Sorting;

use function count;

/**
 * **Utility: Sorting - HeapSort**
 * 
 * Implements the Heap Sort algorithm to sort an array.
 * 
 * The HeapSort algorithm sorts an array by first transforming the array into a max heap and then
 * iteratively swapping the maximum element from the heap with the last unsorted element
 * and "heapifying" the heap again.
 * 
 * **Complexity**: O(n log n) time complexity in all cases (best, average, worst) and O(1) space complexity (in-place sorting).
 */
class HeapSort
{
    /**
     * Sorts an array using the Heap Sort algorithm.
     * @param array $arr The array to be sorted.
     * @return array The sorted array.
     * @throws \UnexpectedValueException If the array has no elements.
     */
    public static function sort(array $arr): array
    {
        $n = count($arr);

        if ($n <= 0) {
            throw new \UnexpectedValueException('Input array must have at least one element.');
        }

        for ($i = floor($n / 2) - 1; $i >= 0; $i--) {
            self::heapify($arr, $n, $i);
        }

        for ($i = $n - 1; $i >= 0; $i--) {
            [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];

            self::heapify($arr, $i, 0);
        }

        return $arr;
    }

    /**
     * Ensures that the array satisfies the heap property.
     * @param array $arr The array to heapify.
     * @param int $n The size of the heap.
     * @param int $i The index to start heapifying from.
     */
    private static function heapify(array &$arr, int $n, int $i): void
    {
        $largest = $i;
        $left = 2 * $i + 1;
        $right = 2 * $i + 2;
        
        if ($left < $n && $arr[$left] > $arr[$largest]) {
            $largest = $left;
        }

        if ($right < $n && $arr[$right] > $arr[$largest]) {
            $largest = $right;
        }

        if ($largest !== $i) {
            [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
            self::heapify($arr, $n, $largest);
        }
    }
}
