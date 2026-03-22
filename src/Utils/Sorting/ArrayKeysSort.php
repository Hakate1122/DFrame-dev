<?php

namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - ArrayKeysSort**
 * 
 * Sorts an array of associative arrays or objects based on specified keys.
 */
class ArrayKeysSort
{
    public const ORDER_ASC  = 'ASC';
    public const ORDER_DESC = 'DESC';

    /**
     * Sorts a collection by specified keys.
     *
     * @param array  $collection      The array of associative arrays or objects to sort.
     * @param array  $keys            The keys to sort by, in order of priority.
     * @param string $order           The sort order: 'ASC' for ascending, 'DESC' for descending.
     * @param bool   $isCaseSensitive Whether the sort should be case-sensitive (for string values).
     *
     * @return array The sorted array.
     *
     * @throws \InvalidArgumentException If a specified key does not exist in the collection items.
     */
    public static function sort(
        array $collection,
        array $keys,
        string $order = self::ORDER_ASC,
        bool $isCaseSensitive = false
    ): array {
        if (empty($collection) || empty($keys)) {
            return $collection;
        }

        usort($collection, function ($a, $b) use ($keys, $order, $isCaseSensitive) {

            foreach ($keys as $key) {
                $v1 = is_array($a) ? ($a[$key] ?? null) : ($a->$key ?? null);
                $v2 = is_array($b) ? ($b[$key] ?? null) : ($b->$key ?? null);

                if ($v1 === null || $v2 === null) {
                    throw new \InvalidArgumentException(
                        "Key '{$key}' does not exist in collection"
                    );
                }

                if (!$isCaseSensitive && is_string($v1) && is_string($v2)) {
                    $v1 = mb_strtolower($v1);
                    $v2 = mb_strtolower($v2);
                }

                if ($v1 === $v2) {
                    continue;
                }

                $cmp = $v1 <=> $v2;

                return $order === self::ORDER_ASC ? $cmp : -$cmp;
            }

            return 0;
        });

        return $collection;
    }

    /**
     * Debug version of array keys sort that reports each comparison step.
     *
     * @param array $collection The array to sort.
     * @param array $keys The keys to sort by.
     * @param string $order The sort order: 'ASC' or 'DESC'.
     * @param bool $isCaseSensitive Whether the sort should be case-sensitive.
     * @param callable|null $onStep Optional callback invoked after each comparison: function(array $current, int $index, int $step).
     *                             If null, the method will echo each step as JSON.
     * @param int $msDelay Optional delay in milliseconds between steps when using the default echo mode.
     * @return array The sorted array.
     */
    public static function debug(
        array $collection,
        array $keys,
        string $order = self::ORDER_ASC,
        bool $isCaseSensitive = false,
        ?callable $onStep = null,
        int $msDelay = 0
    ): array {
        if (empty($collection) || empty($keys)) {
            return $collection;
        }

        usort($collection, function ($a, $b) use ($keys, $order, $isCaseSensitive, $onStep, &$collection, &$step, $msDelay) {

            foreach ($keys as $key) {
                $v1 = is_array($a) ? ($a[$key] ?? null) : ($a->$key ?? null);
                $v2 = is_array($b) ? ($b[$key] ?? null) : ($b->$key ?? null);

                if ($v1 === null || $v2 === null) {
                    throw new \InvalidArgumentException(
                        "Key '{$key}' does not exist in collection"
                    );
                }

                if (!$isCaseSensitive && is_string($v1) && is_string($v2)) {
                    $v1 = mb_strtolower($v1);
                    $v2 = mb_strtolower($v2);
                }

                if ($v1 === $v2) {
                    continue;
                }

                $cmp = $v1 <=> $v2;

                if (is_callable($onStep)) {
                    static $step = 0;
                    $step++;
                    call_user_func($onStep, $collection, null, $step);
                    if ($msDelay > 0) {
                        usleep($msDelay * 1000);
                    }
                }

                return $order === self::ORDER_ASC ? $cmp : -$cmp;
            }

            return 0;
        });

        return $collection;
    }
}
