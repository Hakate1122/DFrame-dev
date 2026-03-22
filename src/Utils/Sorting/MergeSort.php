<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - MergeSort**
 * 
 * Implements the Merge Sort algorithm to sort an array.
 */
class MergeSort
{
    /**
     * Sorts an array using the Merge Sort algorithm.
     * @param array $arr The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $arr): array
    {
        if (count($arr) <= 1) {
            return $arr;
        }

        $mid = floor(count($arr) / 2);
        $leftArray = self::sort(array_slice($arr, 0, $mid));
        $rightArray = self::sort(array_slice($arr, $mid));

        return self::merge($leftArray, $rightArray);
    }

    /**
     * Merges two sorted arrays into one sorted array.
     * @param array $leftArray The left sorted array.
     * @param array $rightArray The right sorted array.
     * @return array The merged sorted array.
     */
    private static function merge(array $leftArray, array $rightArray): array
    {
        $result = [];
        $i = 0;
        $j = 0;

        while ($i < count($leftArray) && $j < count($rightArray)) {
            if ($rightArray[$j] > $leftArray[$i]) {
                $result[] = $leftArray[$i];
                $i++;
            } else {
                $result[] = $rightArray[$j];
                $j++;
            }
        }

        while ($i < count($leftArray)) {
            $result[] = $leftArray[$i];
            $i++;
        }

        while ($j < count($rightArray)) {
            $result[] = $rightArray[$j];
            $j++;
        }

        return $result;
    }

    /**
     * Debug version of Merge Sort that reports merging steps.
     *
     * @param array $arr The array to sort.
     * @param callable|null $onStep Optional callback invoked during merges: function(array $current, int $step, string $status = null).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @param int|null $memoryLimitBytes Optional memory limit in bytes; stops when exceeded.
     * @return array The sorted array.
     */
    public static function debug(array $arr, ?callable $onStep = null, int $msDelay = 0, ?int $memoryLimitBytes = null): array
    {
        $step = 0;

        $recur = function(array $a) use (&$recur, &$step, $onStep, $msDelay, $memoryLimitBytes) : array {
            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                $status = 'memory_limit_exceeded';
                if ($onStep) {
                    $onStep($a, $step, $status);
                } else {
                    echo json_encode(['status' => $status, 'step' => $step, 'array' => $a]) . PHP_EOL;
                }
                return $a;
            }

            if (count($a) <= 1) return $a;
            $mid = floor(count($a) / 2);
            $left = $recur(array_slice($a, 0, $mid));
            $right = $recur(array_slice($a, $mid));

            $merged = [];
            $i = 0; $j = 0;
            while ($i < count($left) && $j < count($right)) {
                if ($right[$j] > $left[$i]) {
                    $merged[] = $left[$i++];
                } else {
                    $merged[] = $right[$j++];
                }
                $step++;
                if ($onStep) {
                    $onStep(array_merge($merged, array_slice($left, $i), array_slice($right, $j)), $step);
                } else {
                    echo json_encode(['step' => $step, 'current' => array_merge($merged, array_slice($left, $i), array_slice($right, $j))]) . PHP_EOL;
                    if ($msDelay > 0) usleep($msDelay * 1000);
                }
            }

            while ($i < count($left)) { $merged[] = $left[$i++]; }
            while ($j < count($right)) { $merged[] = $right[$j++]; }

            return $merged;
        };

        return $recur($arr);
    }
}
