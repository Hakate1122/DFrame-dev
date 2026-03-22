<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - HeapSort**
 * 
 * Implements the Heap Sort algorithm to sort an array.
 * 
 * The HeapSort algorithm sorts an array by first transforming the array into a max heap and then
 * iteratively swapping the maximum element from the heap with the last unsorted element
 * and "heapifying" the heap again.
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
        // Get the number of elements in the array.
        $n = count($arr);

        // Throw an exception if the array has no elements.
        if ($n <= 0) {
            throw new \UnexpectedValueException('Input array must have at least one element.');
        }

        // Build a max heap from the array.
        for ($i = floor($n / 2) - 1; $i >= 0; $i--) {
            self::heapify($arr, $n, $i);
        }

        // Extract elements from the max heap and build the sorted array.
        for ($i = $n - 1; $i >= 0; $i--) {
            // Swap the root(maximum value) of the heap with the last element of the heap.
            [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];

            // Heapify the reduced heap.
            self::heapify($arr, $i, 0);
        }

        // Return the sorted array.
        return $arr;
    }

    /**
     * Debug version of Heap Sort which reports swaps during heap construction and extraction.
     *
     * @param array $arr The array to sort.
     * @param callable|null $onStep Optional callback invoked after each swap: function(array $current, int $index, int $step, string $status = null).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @param int|null $memoryLimitBytes Optional memory limit in bytes; stops when exceeded.
     * @return array The sorted array.
     */
    public static function debug(array $arr, ?callable $onStep = null, int $msDelay = 0, ?int $memoryLimitBytes = null): array
    {
        $n = count($arr);
        $step = 0;

        if ($n <= 0) {
            throw new \UnexpectedValueException('Input array must have at least one element.');
        }

        // Build max heap
        for ($i = floor($n / 2) - 1; $i >= 0; $i--) {
            self::heapifyDebug($arr, $n, $i, $onStep, $msDelay, $memoryLimitBytes, $step);
        }

        // Extract elements from heap
        for ($i = $n - 1; $i >= 0; $i--) {
            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                $status = 'memory_limit_exceeded';
                if ($onStep) {
                    $onStep($arr, $i, $step, $status);
                } else {
                    echo json_encode(['status' => $status, 'step' => $step, 'array' => $arr]) . PHP_EOL;
                }
                break;
            }

            [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];
            $step++;
            if ($onStep) {
                $onStep($arr, $i, $step);
            } else {
                echo json_encode(['step' => $step, 'swapped_index' => $i, 'current' => $arr]) . PHP_EOL;
                if ($msDelay > 0) usleep($msDelay * 1000);
            }

            self::heapifyDebug($arr, $i, 0, $onStep, $msDelay, $memoryLimitBytes, $step);
        }

        return $arr;
    }

    private static function heapifyDebug(array &$arr, int $n, int $i, ?callable $onStep, int $msDelay, ?int $memoryLimitBytes, int &$step): void
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
            $step++;
            if ($onStep) {
                $onStep($arr, $largest, $step);
            } else {
                echo json_encode(['step' => $step, 'swapped_index' => $largest, 'current' => $arr]) . PHP_EOL;
                if ($msDelay > 0) usleep($msDelay * 1000);
            }

            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                return;
            }

            self::heapifyDebug($arr, $n, $largest, $onStep, $msDelay, $memoryLimitBytes, $step);
        }
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
