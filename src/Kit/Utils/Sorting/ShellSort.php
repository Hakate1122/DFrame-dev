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
