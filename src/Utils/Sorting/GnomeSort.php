<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - GnomeSort**
 * 
 * Implements the Gnome Sort algorithm to sort an array.
 * 
 * The Gnome algorithm works by locating the first instance in which two adjoining elements 
 * are arranged incorrectly and swaps with each other.
 * 
 * References: https://www.geeksforgeeks.org/gnome-sort-a-stupid-one/
 */
class GnomeSort
{
    /**
     * Sorts an array using the Gnome Sort algorithm.
     * @param array $array The array to be sorted.
     * @return array The sorted array.
     */
    public static function sort(array $array): array
    {
        $a = 1;
        $b = 2;

        while ($a < count($array)) {
            if ($array[$a - 1] <= $array[$a]) {
                $a = $b;
                $b++;
            } else {
                list($array[$a], $array[$a - 1]) = array($array[$a - 1], $array[$a]);
                $a--;
                if ($a == 0) {
                    $a = $b;
                    $b++;
                }
            }
        }

        return $array;
    }
}
