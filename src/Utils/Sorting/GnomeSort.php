<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - GnomeSort**
 * 
 * Implements the Gnome Sort algorithm to sort an array.
 * 
 * The Gnome algorithm works by locating the first instance in which two adjoining elements 
 * are arranged incorrectly and swaps with each other.
 * 
 * References: https://www.geeksforgeeks.org/gnome-sort-a-stupid-one/
 */
class GnomeSort
{
    /**
     * Sorts an array using the Gnome Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $array): array
    {
        $a = 1;
        $b = 2;

        while ($a < count($array)) {
            if ($array[$a - 1] <= $array[$a]) {
                $a = $b;
                $b++;
            } else {
                list($array[$a], $array[$a - 1]) = array($array[$a - 1], $array[$a]);
                $a--;
                if ($a == 0) {
                    $a = $b;
                    $b++;
                }
            }
        }

        return $array;
    }

    /**
     * Debug version of Gnome Sort that reports each swap step.
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
        $a = 1;
        $b = 2;
        $step = 0;

        while ($a < count($array)) {
            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                $status = 'memory_limit_exceeded';
                if ($onStep) {
                    $onStep($array, $a, $step, $status);
                } else {
                    echo json_encode(['status' => $status, 'step' => $step, 'array' => $array]) . PHP_EOL;
                }
                break;
            }

            if ($array[$a - 1] <= $array[$a]) {
                $a = $b;
                $b++;
            } else {
                list($array[$a], $array[$a - 1]) = array($array[$a - 1], $array[$a]);
                $a--;
                $step++;
                if ($onStep) {
                    $onStep($array, $a, $step);
                } else {
                    echo json_encode(['step' => $step, 'index' => $a, 'current' => $array]) . PHP_EOL;
                    if ($msDelay > 0) usleep($msDelay * 1000);
                }

                if ($a == 0) {
                    $a = $b;
                    $b++;
                }
            }
        }

        return $array;
    }
}
