<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - Bucket Sort**
 * 
 * Implements the Bucket Sort algorithm to sort an array of floating-point numbers uniformly distributed over the interval [0, 1).
 * 
 * **Principle**: Bucket Sort works by distributing the elements of the input array into a number of buckets. Each bucket is then sorted individually, either using a different sorting algorithm or by recursively applying the bucket sort. Finally, the sorted buckets are concatenated to produce the final sorted array.
 * 
 * **Complexity**: O(n + k) time complexity, where n is the number of elements in the input array and k is the number of buckets. The space complexity is O(n + k) due to the additional space used for the buckets.
 * 
 * **Note**: If want to sort integers or other types of data, the algorithm can be adapted by changing the way elements are distributed into buckets and how the buckets are sorted.
 */

class BucketSort
{
    public static function sort(array $arr): array
    {
        if (empty($arr)) return $arr;

        $n = count($arr);
        $buckets = array_fill(0, $n, []);

        foreach ($arr as $value) {
            if ($value < 0 || $value > 1) {
                throw new \InvalidArgumentException(
                    "BucketSort expects values in [0,1]"
                );
            }

            $index = min((int)($value * $n), $n - 1);
            $buckets[$index][] = $value;
        }

        $result = [];

        foreach ($buckets as $bucket) {
            // Có thể thay bằng insertion sort nếu muốn thuần algorithm
            sort($bucket);
            foreach ($bucket as $v) {
                $result[] = $v;
            }
        }

        return $result;
    }
}