<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - BubbleSort2**
 * 
 * Implements the Bubble Sort algorithm to sort an array.
 */
class BubbleSort2
{
    /**
     * Sorts an array using the Bubble Sort algorithm.
     * @param array $input The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $input)
    {
        if (!isset($input)) {
            return [];
        }

        do {
            $swapped = false;

            for ($i = 0, $count = sizeof($input) - 1; $i < $count; $i++) {
                if ($input[$i + 1] < $input[$i]) {
                    list($input[$i + 1], $input[$i]) = [$input[$i], $input[$i + 1]];
                    $swapped = true;
                }
            }
        } while ($swapped);
        return $input;
    }

    /**
     * Debug version of bubble sort that reports each swap step.
     *
     * @param array $input The array to sort.
     * @param callable|null $onStep Optional callback invoked after each swap: function(array $current, int $index, int $step).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @return array The sorted array.
     */
    public static function debug(array $input, ?callable $onStep = null, int $msDelay = 0)
    {
        if (!isset($input)) {
            return [];
        }

        $step = 0;

        do {
            $swapped = false;

            for ($i = 0, $count = sizeof($input) - 1; $i < $count; $i++) {
                if ($input[$i + 1] < $input[$i]) {
                    list($input[$i + 1], $input[$i]) = [$input[$i], $input[$i + 1]];
                    $swapped = true;
                    $step++;

                    if ($onStep !== null) {
                        call_user_func($onStep, $input, $i, $step);
                    } else {
                        echo "Step {$step}: " . json_encode($input, JSON_UNESCAPED_UNICODE) . PHP_EOL;
                        if ($msDelay > 0) {
                            usleep($msDelay * 1000);
                        }
                    }
                }
            }
        } while ($swapped);

        return $input;
    }
}
