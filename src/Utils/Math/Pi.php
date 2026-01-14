<?php

namespace DFrame\Utils\Math;

/**
 * **Utility: Math - Pi**
 * 
 * Provides the value of Pi.
 */

class Pi
{
    /**
     * Returns the default value of Pi - PHP constant.
     *
     * @return float The value of Pi.
     */
    public static function default(): float
    {
        return M_PI;
    }

    /**
     * Calculates Pi using the Leibniz formula.
     *
     * @param int $iterations The number of iterations to perform for the approximation.
     * @return float The approximated value of Pi.
     */
    public static function leibniz(int $iterations = 1000000): float
    {
        $pi = 0.0;
        $sign = 1.0;

        for ($k = 0; $k < $iterations; $k++) {
            $pi += $sign / (2 * $k + 1);
            $sign = -$sign;
        }

        return 4 * $pi;
    }

    /**
     * Helper function to compute arctan using Taylor series.
     *
     * @param string $x     The input value as a string.
     * @param int    $scale The scale for bc math operations.
     * @return string The computed arctan value as a string.
     */
    private static function arctan(string $x, int $scale): string
    {
        bcscale($scale + 5); // Tăng scale đệm để tránh sai số làm tròn cuối
        $x2 = bcmul($x, $x);
        $term = $x;
        $result = $x;
        $n = 1;

        while (true) {
            $term = bcmul($term, $x2);

            $divisor = (string)(2 * $n + 1);

            $current_term = bcdiv($term, $divisor);

            if (bccomp($current_term, '0') === 0) {
                break;
            }

            if ($n % 2 != 0) {
                $result = bcsub($result, $current_term);
            } else {
                $result = bcadd($result, $current_term);
            }

            $n++;
        }

        return $result;
    }

    /**
     * Calculates Pi to a high precision using the Machin-like formula.
     *
     * @param int $digits The number of decimal places.
     * @return string The value of Pi to the specified precision.
     */
    public static function highPrecision(int $digits = 50): string
    {
        bcscale($digits + 2);

        $a = self::arctan(bcdiv('1', '5', $digits + 2), $digits);
        $b = self::arctan(bcdiv('1', '239', $digits + 2), $digits);

        return bcsub(
            bcmul('16', $a),
            bcmul('4', $b),
            $digits
        );
    }
}
