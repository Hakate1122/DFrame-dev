<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - ShellSort**
 * 
 * Implements the Shell Sort algorithm to sort an array.
 * 
 * This function sorts an array in ascending order using the Shell Sort algorithm.
 * Time complexity of the Shell Sort algorithm depends on the gap sequence used.
 * With Knuth's sequence, the time complexity is O(n^(3/2)).
 */
class ShellSort
{
    /**
     * Sorts an array using the Shell Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $array): array
    {
        $length = count($array);
        $series = self::calculateKnuthSeries($length);

        foreach ($series as $gap) {
            for ($i = $gap; $i < $length; $i++) {
                $temp = $array[$i];
                $j = $i;

                while ($j >= $gap && $array[$j - $gap] > $temp) {
                    $array[$j] = $array[$j - $gap];
                    $j -= $gap;
                }

                $array[$j] = $temp;
            }
        }

        return $array;
    }

    /**
     * Debug version of Shell Sort that reports each insertion/move step.
     *
     * @param array $array The array to sort.
     * @param callable|null $onStep Optional callback invoked after each move: function(array $current, int $index, int $step, string $status = null).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @param int|null $memoryLimitBytes Optional memory limit in bytes; stops when exceeded.
     * @return array The sorted array.
     */
    public static function debug(array $array, ?callable $onStep = null, int $msDelay = 0, ?int $memoryLimitBytes = null): array
    {
        $length = count($array);
        $series = self::calculateKnuthSeries($length);
        $step = 0;

        foreach ($series as $gap) {
            for ($i = $gap; $i < $length; $i++) {
                if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                    $status = 'memory_limit_exceeded';
                    if ($onStep) {
                        $onStep($array, $i, $step, $status);
                    } else {
                        echo json_encode(['status' => $status, 'step' => $step, 'array' => $array]) . PHP_EOL;
                    }
                    return $array;
                }

                $temp = $array[$i];
                $j = $i;

                while ($j >= $gap && $array[$j - $gap] > $temp) {
                    $array[$j] = $array[$j - $gap];
                    $j -= $gap;
                    $step++;
                    if ($onStep) {
                        $onStep($array, $j, $step);
                    } else {
                        echo json_encode(['step' => $step, 'move_index' => $j, 'current' => $array]) . PHP_EOL;
                        if ($msDelay > 0) usleep($msDelay * 1000);
                    }
                }

                $array[$j] = $temp;
            }
        }

        return $array;
    }

    /**
     * Calculate Knuth's series for gap sequence.
     * @param int $n Size of the array.
     * @return array The gap sequence.
     */
    private static function calculateKnuthSeries(int $n): array
    {
        $h = 1;
        $series = [];

        while ($h < $n) {
            array_unshift($series, $h);
            $h = 3 * $h + 1;
        }

        return $series;
    }
}
