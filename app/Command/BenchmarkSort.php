<?php

namespace App\Command;

use DFrame\Utils\Sorting\ArrayKeysSort;
use DFrame\Utils\Sorting\BubbleSort;
use DFrame\Utils\Sorting\BubbleSort2;
use DFrame\Utils\Sorting\CountSort;
use DFrame\Utils\Sorting\GnomeSort;
use DFrame\Utils\Sorting\HeapSort;
use DFrame\Utils\Sorting\InsertionSort;
use DFrame\Utils\Sorting\MergeSort;
use DFrame\Utils\Sorting\QuickSort;
use DFrame\Utils\Sorting\RadixSort;
use DFrame\Utils\Sorting\SelectionSort;
use DFrame\Utils\Sorting\ShellSort;
use DFrame\Utils\Sorting\BogoSort;

class BenchmarkSort
{
    public static function handle()
    {
        $algorithms = [
            'GnomeSort' => GnomeSort::class,
            'BubbleSort' => BubbleSort::class,
            'BubbleSort2' => BubbleSort2::class,
            'InsertionSort' => InsertionSort::class,
            'SelectionSort' => SelectionSort::class,
            'ShellSort' => ShellSort::class,
            'MergeSort' => MergeSort::class,
            'QuickSort' => QuickSort::class,
            'HeapSort' => HeapSort::class,
            'CountSort' => CountSort::class,
            'RadixSort' => RadixSort::class,
            'ArrayKeysSort' => ArrayKeysSort::class,
            // 'BogoSort' => BogoSort::class,
        ];

        $defaultSizes = [1, 10, 20];
        $sizes = $defaultSizes;
        // Use $_SERVER['argv'] for CLI arguments to avoid undefined $argv
        $cliArgs = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
        if (isset($cliArgs[1])) {
            $parts = array_filter(array_map('trim', explode(',', $cliArgs[1])));
            $tmp = [];
            foreach ($parts as $p) {
                $v = (int)$p;
                if ($v > 0) $tmp[] = $v;
            }
            if (count($tmp) > 0) $sizes = $tmp;
        }
        $repeats = 5;

        echo "Sorting benchmark\n";
        echo "Sizes: " . implode(', ', $sizes) . "; Repeats: $repeats\n\n";

        $results = [];

        foreach ($sizes as $n) {
            echo "Size: $n\n";
            // initialize per-size results
            foreach ($algorithms as $name => $class) {
                $results[$n][$name] = [];
            }

            for ($r = 0; $r < $repeats; $r++) {
                // generate a random integer array (non-negative) suitable for radix/count sorts
                $maxVal = max(100, (int)($n * 10));
                $baseArray = [];
                for ($i = 0; $i < $n; $i++) {
                    $baseArray[] = random_int(0, $maxVal);
                }

                foreach ($algorithms as $name => $class) {
                    if (!class_exists($class) || !method_exists($class, 'sort')) {
                        $results[$n][$name][] = null;
                        continue;
                    }

                    // skip sorts that require more than one parameter (e.g. ArrayKeysSort)
                    try {
                        $ref = new \ReflectionMethod($class, 'sort');
                        if ($ref->getNumberOfRequiredParameters() > 1) {
                            $results[$n][$name][] = null;
                            continue;
                        }
                    } catch (\ReflectionException $e) {
                        $results[$n][$name][] = null;
                        continue;
                    }

                    $arr = $baseArray; // copy

                    try {
                        $t0 = hrtime(true);
                        $sorted = $class::sort($arr);
                        $t1 = hrtime(true);
                        $deltaNs = $t1 - $t0;
                        // store microseconds
                        $results[$n][$name][] = $deltaNs / 1000.0;
                    } catch (\Throwable $e) {
                        $results[$n][$name][] = null;
                        echo "  $name threw: " . $e->getMessage() . "\n";
                    }
                }
            }

            // compute averages and print
            foreach ($algorithms as $name => $class) {
                $times = array_filter($results[$n][$name], function ($v) {
                    return is_numeric($v);
                });
                if (count($times) === 0) {
                    printf("  %-15s : %s\n", $name, 'n/a');
                    continue;
                }
                $avg = array_sum($times) / count($times);
                $min = min($times);
                $max = max($times);
                // show avg in milliseconds with 3 decimals
                printf("  %-15s : avg %8.3f ms | min %8.3f ms | max %8.3f ms\n", $name, $avg / 1000.0, $min / 1000.0, $max / 1000.0);
            }

            echo "\n";
        }

        $summaryPath = ROOT_DIR . '/benchmark_results.json';
        file_put_contents($summaryPath, json_encode($results, JSON_PRETTY_PRINT));
        echo "Results written to scripts/benchmark_results.json\n";

        echo "Done.\n";
    }
}
