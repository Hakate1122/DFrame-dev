<?php

namespace DFrame\Utils\Math;
/**
 * **Utility: Math - Delta**
 * 
 * Provides methods to calculate the difference (delta) between two numbers.
 */
class Delta
{
    /**
     * Calculates the absolute difference between two numbers.
     *
     * @param float $a The first number.
     * @param float $b The second number.
     * @return float The absolute difference between the two numbers.
     */
    public static function absolute(float $a, float $b): float
    {
        return abs($a - $b);
    }

    /**
     * Calculates the relative difference between two numbers as a percentage.
     *
     * @param float $a The first number.
     * @param float $b The second number.
     * @return float The relative difference as a percentage.
     */
    public static function relative(float $a, float $b): float
    {
        if ($b == 0) {
            throw new \InvalidArgumentException("The second number (b) cannot be zero for relative difference calculation.");
        }
        return (abs($a - $b) / abs($b)) * 100;
    }
}