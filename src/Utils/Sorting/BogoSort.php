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
}