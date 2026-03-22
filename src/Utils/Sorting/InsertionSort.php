<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - InsertionSort**
 * 
 * Implements the Insertion Sort algorithm to sort an array.
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

    /**
     * Debug version of Insertion Sort that reports each insertion step.
     *
     * @param array $array The array to sort.
     * @param callable|null $onStep Optional callback invoked after each insertion: function(array $current, int $index, int $step, string $status = null).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @param int|null $memoryLimitBytes Optional memory limit in bytes; stops when exceeded.
     * @return array The sorted array.
     */
    public static function debug(array $array, ?callable $onStep = null, int $msDelay = 0, ?int $memoryLimitBytes = null): array
    {
        $n = count($array);
        $step = 0;

        for ($i = 1; $i < $n; $i++) {
            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                $status = 'memory_limit_exceeded';
                if ($onStep) {
                    $onStep($array, $i, $step, $status);
                } else {
                    echo json_encode(['status' => $status, 'step' => $step, 'array' => $array]) . PHP_EOL;
                }
                break;
            }

            $currentVal = $array[$i];
            for ($j = $i - 1; $j >= 0 && $array[$j] > $currentVal; $j--) {
                $array[$j + 1] = $array[$j];
            }

            $array[$j + 1] = $currentVal;
            $step++;

            if ($onStep) {
                $onStep($array, $j + 1, $step);
            } else {
                echo json_encode(['step' => $step, 'inserted_index' => $j + 1, 'current' => $array]) . PHP_EOL;
                if ($msDelay > 0) usleep($msDelay * 1000);
            }
        }

        return $array;
    }
}
