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
}
