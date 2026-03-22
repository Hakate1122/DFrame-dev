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

    /**
     * Debug version of Quick Sort that reports partition/merge steps.
     *
     * @param array $input The array to sort.
     * @param callable|null $onStep Optional callback invoked after partition/concat: function(array $current, int $step, string $status = null).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @param int|null $memoryLimitBytes Optional memory limit in bytes; stops when exceeded.
     * @return array The sorted array.
     */
    public static function debug(array $input, ?callable $onStep = null, int $msDelay = 0, ?int $memoryLimitBytes = null): array
    {
        $step = 0;

        $recur = function(array $arr) use (&$recur, &$step, $onStep, $msDelay, $memoryLimitBytes): array {
            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                $status = 'memory_limit_exceeded';
                if ($onStep) {
                    $onStep($arr, $step, $status);
                } else {
                    echo json_encode(['status' => $status, 'step' => $step, 'array' => $arr]) . PHP_EOL;
                }
                return $arr;
            }

            if (empty($arr)) return [];
            if (count($arr) < 2) return $arr;

            $shift = array_shift($arr);
            $lt = [];
            $gt = [];
            foreach ($arr as $value) {
                $value <= $shift ? $lt[] = $value : $gt[] = $value;
            }

            $left = $recur($lt);
            $right = $recur($gt);
            $merged = array_merge($left, [$shift], $right);
            $step++;
            if ($onStep) {
                $onStep($merged, $step);
            } else {
                echo json_encode(['step' => $step, 'current' => $merged]) . PHP_EOL;
                if ($msDelay > 0) usleep($msDelay * 1000);
            }

            return $merged;
        };

        return $recur($input);
    }
}
