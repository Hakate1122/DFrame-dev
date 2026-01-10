<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - RadixSort**
 * 
 * Implements the Radix Sort algorithm to sort an array.
 */
class RadixSort
{
    /**
     * Sorts an array using the Radix Sort algorithm.
     * @param array $nums The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $nums): array
    {
        $maxDigitsCount = self::maxDigits($nums);
        for ($k = 0; $k < $maxDigitsCount; $k++) {
            $digitBucket = array_fill(0, 10, []);

            for ($i = 0; $i < count($nums); $i++) {
                $digitBucket[self::getDigit($nums[$i], $k)][] = $nums[$i];
            }

            $nums = self::concat($digitBucket);
        }

        return $nums;
    }

    /**
     * Get the digits value by it's place.
     * @param int $num The number.
     * @param int $i The place index.
     * @return int The digit at the specified place.
     */
    private static function getDigit(int $num, int $i): int
    {
        return floor(abs($num) / pow(10, $i)) % 10;
    }

    /**
     * Get the digits count.
     * @param int $num The number.
     * @return int The number of digits.
     */
    private static function digitsCount(int $num): int
    {
        if ($num == 0) {
            return 1;
        }
        return floor(log10(abs($num))) + 1;
    }

    /**
     * Get the max digits count in the array.
     * @param array $arr The array.
     * @return int The maximum number of digits.
     */
    private static function maxDigits(array $arr): int
    {
        $maxDigits = 0;

        for ($i = 0; $i < count($arr); $i++) {
            $maxDigits = max($maxDigits, self::digitsCount($arr[$i]));
        }

        return $maxDigits;
    }

    /**
     * Concatenate a 2D array into a 1D array.
     * @param array $array The 2D array.
     * @return array The concatenated 1D array.
     */
    private static function concat(array $array): array
    {
        $newArray = [];

        for ($i = 0; $i < count($array); $i++) {
            for ($j = 0; $j < count($array[$i]); $j++) {
                $newArray[] = $array[$i][$j];
            }
        }
        
        return $newArray;
    }
}
