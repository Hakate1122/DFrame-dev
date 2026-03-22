<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - BubbleSort**
 * 
 * Implements the Bubble Sort algorithm to sort an array.
 */
class BubbleSort
{
    /**
     * Sorts an array using the Bubble Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort($array)
    {
        $length = count($array);

        for ($i = $length; $i > 0; $i--) {
            $swapped = true;

            for ($j = 0; $j < $i - 1; $j++) {
                if ($array[$j] > $array[$j + 1]) {
                    $temp = $array[$j];
                    $array[$j] = $array[$j + 1];
                    $array[$j + 1] = $temp;
                    $swapped = false;
                }
            }

            if ($swapped) {
                break;
            }
        }

        return $array;
    }

    /**
     * Debug version of bubble sort that reports each swap step.
     *
     * @param array $array The array to sort.
     * @param callable|null $onStep Optional callback invoked after each swap: function(array $current, int $index, int $step).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @return array The sorted array.
     */
    public static function debug(array $array, ?callable $onStep = null, int $msDelay = 0)
    {
        $length = count($array);
        $step = 0;

        for ($i = $length; $i > 0; $i--) {
            $swapped = true;

            for ($j = 0; $j < $i - 1; $j++) {
                if ($array[$j] > $array[$j + 1]) {
                    $temp = $array[$j];
                    $array[$j] = $array[$j + 1];
                    $array[$j + 1] = $temp;
                    $swapped = false;

                    if ($onStep) {
                        $onStep($array, $j, ++$step);
                    } else {
                        echo json_encode([
                            'step' => $step,
                            'index' => $j,
                            'current' => $array
                        ]) . "\n";
                        if ($msDelay > 0) {
                            usleep($msDelay * 1000);
                        }
                    }
                }
            }

            if ($swapped) {
                break;
            }
        }

        return $array;
    }
}
