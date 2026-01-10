<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - MergeSort**
 * 
 * Implements the Merge Sort algorithm to sort an array.
 */
class MergeSort
{
    /**
     * Sorts an array using the Merge Sort algorithm.
     * @param array $arr The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $arr): array
    {
        if (count($arr) <= 1) {
            return $arr;
        }

        $mid = floor(count($arr) / 2);
        $leftArray = self::sort(array_slice($arr, 0, $mid));
        $rightArray = self::sort(array_slice($arr, $mid));

        return self::merge($leftArray, $rightArray);
    }

    /**
     * Merges two sorted arrays into one sorted array.
     * @param array $leftArray The left sorted array.
     * @param array $rightArray The right sorted array.
     * @return array The merged sorted array.
     */
    private static function merge(array $leftArray, array $rightArray): array
    {
        $result = [];
        $i = 0;
        $j = 0;

        while ($i < count($leftArray) && $j < count($rightArray)) {
            if ($rightArray[$j] > $leftArray[$i]) {
                $result[] = $leftArray[$i];
                $i++;
            } else {
                $result[] = $rightArray[$j];
                $j++;
            }
        }

        while ($i < count($leftArray)) {
            $result[] = $leftArray[$i];
            $i++;
        }

        while ($j < count($rightArray)) {
            $result[] = $rightArray[$j];
            $j++;
        }

        return $result;
    }
}
