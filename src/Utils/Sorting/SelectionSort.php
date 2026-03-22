<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - SelectionSort**
 * 
 * Implements the Selection Sort algorithm to sort an array.
 */
class SelectionSort
{
    /**
     * Sorts an array using the Selection Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $array): array
    {
        $length = count($array);

        for ($i = 0; $i < $length; $i++) {
            $lowest = $i;

            for ($j = $i + 1; $j < $length; $j++) {
                if ($array[$j] < $array[$lowest]) {
                    $lowest = $j;
                }
            }

            if ($i !== $lowest) {
                $temp = $array[$i];
                $array[$i] = $array[$lowest];
                $array[$lowest] = $temp;
            }
        }

        return $array;
    }

    /**
     * Debug version of Selection Sort that reports each swap.
     *
     * @param array $array The array to sort.
     * @param callable|null $onStep Optional callback invoked after each swap: function(array $current, int $index, int $step, string $status = null).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @param int|null $memoryLimitBytes Optional memory limit in bytes; stops when exceeded.
     * @return array The sorted array.
     */
    public static function debug(array $array, ?callable $onStep = null, int $msDelay = 0, ?int $memoryLimitBytes = null): array
    {
        $length = count($array);
        $step = 0;

        for ($i = 0; $i < $length; $i++) {
            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                $status = 'memory_limit_exceeded';
                if ($onStep) {
                    $onStep($array, $i, $step, $status);
                } else {
                    echo json_encode(['status' => $status, 'step' => $step, 'array' => $array]) . PHP_EOL;
                }
                break;
            }

            $lowest = $i;

            for ($j = $i + 1; $j < $length; $j++) {
                if ($array[$j] < $array[$lowest]) {
                    $lowest = $j;
                }
            }

            if ($i !== $lowest) {
                $temp = $array[$i];
                $array[$i] = $array[$lowest];
                $array[$lowest] = $temp;
                $step++;

                if ($onStep) {
                    $onStep($array, $i, $step);
                } else {
                    echo json_encode(['step' => $step, 'swapped_index' => $i, 'current' => $array]) . PHP_EOL;
                    if ($msDelay > 0) usleep($msDelay * 1000);
                }
            }
        }

        return $array;
    }
}
