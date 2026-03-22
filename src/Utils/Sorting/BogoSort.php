<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - BogoSort**
 * 
 * Implements the Bogo Sort algorithm to sort an array.
 * 
 * **Caution**: Bogo Sort is highly inefficient and is used for educational purposes only.
 */

class BogoSort
{
    /**
     * Checks if the array is sorted.
     * @param array $array The array to check.
     * @return bool True if sorted, false otherwise.
     */
    private static function isSorted($array)
    {
        for ($i = 0; $i < count($array) - 1; $i++) {
            if ($array[$i] > $array[$i + 1]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Shuffles the array randomly.
     * @param array $array The array to shuffle.
     * @return array The shuffled array.
     */
    private static function shuffle($array)
    {
        shuffle($array);
        return $array;
    }

    /**
     * Sorts an array using the Bogo Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort($array)
    {
        while (!self::isSorted($array)) {
            $array = self::shuffle($array);
        }
        return $array;
    }

    /**
     * Debug version of Bogo Sort that reports each shuffle step.
     *
     * @param array $array The array to sort.
     * @param callable|null $onStep Optional callback invoked after each shuffle: function(array $current, int $step, string $status = null).
     *                             If null, the method will echo each step as JSON.
     * @param int|null $memoryLimitBytes Optional memory limit in bytes. If provided and current memory usage
     *                                   exceeds this limit the debug run will stop and report the condition.
     * @return array The sorted array.
     */
    public static function debug(array $array, ?callable $onStep = null, ?int $memoryLimitBytes = null)
    {
        $step = 0;
        while (!self::isSorted($array)) {
            // Check memory limit if provided
            if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
                $msg = 'Memory limit exceeded';
                if ($onStep) {
                    $onStep($array, $step, $msg);
                } else {
                    $usage = memory_get_usage(true);
                    echo "{$msg} at step {$step} (usage={$usage} bytes, limit={$memoryLimitBytes} bytes)" . PHP_EOL;
                }
                break;
            }

            $array = self::shuffle($array);
            $step++;
            if ($onStep) {
                $onStep($array, $step);
            } else {
                echo "Step $step: " . json_encode($array) . PHP_EOL;
            }
        }
        return $array;
    }
}