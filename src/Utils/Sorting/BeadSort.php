<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - BeadSort**
 * 
 * Implements the Bead Sort algorithm to sort an array of non-negative integers.
 * 
 * **Principle**: Bead Sort, also known as Gravity Sort, simulates the natural process of beads falling under gravity. Each integer in the input array is represented as a column of beads. The beads are allowed to fall, and the resulting configuration is read off to produce the sorted output.
 * 
 * **Complexity**: O(n + m) time complexity, where n is the number of elements in the input array and m is the maximum value in the array. The space complexity is O(n * m) due to the 2D array used to represent the beads.
 * 
 * **Requirements**: The input array must consist of non-negative integers, as the algorithm relies on representing each integer as a column of beads.
 */
class BeadSort{
     public static function sort(array $arr): array
    {
        if ($arr === []) {
            return $arr;
        }

        $max = max($arr);
        $n = count($arr);

        $beads = array_fill(0, $n, array_fill(0, $max, 0));

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $arr[$i]; $j++) {
                $beads[$i][$j] = 1;
            }
        }

        for ($j = 0; $j < $max; $j++) {
            $sum = 0;
            for ($i = 0; $i < $n; $i++) {
                $sum += $beads[$i][$j];
                $beads[$i][$j] = 0;
            }
            for ($i = $n - $sum; $i < $n; $i++) {
                $beads[$i][$j] = 1;
            }
        }

        for ($i = 0; $i < $n; $i++) {
            $count = 0;
            for ($j = 0; $j < $max; $j++) {
                $count += $beads[$i][$j];
            }
            $arr[$i] = $count;
        }

        return $arr;
    }
}