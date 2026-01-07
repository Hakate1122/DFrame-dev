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
}
