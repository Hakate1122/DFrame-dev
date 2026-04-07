<?php

namespace App\Command;

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
use DFrame\Utils\Sorting\TimSort;

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
            'TimSort' => TimSort::class,
            // 'BogoSort' => BogoSort::class,
        ];

        $defaultSizes = [10, 100, 1000];
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
                    } catch (\ReflectionException $e) {
                        $results[$n][$name][] = null;
                        continue;
                    }

                    if ($ref->getNumberOfRequiredParameters() > 1) {
                        $results[$n][$name][] = null;
                        continue;
                    }

                    $arr = $baseArray; // copy

                    // skip extremely slow algorithms for larger sizes
                    if ($name === 'BogoSort' && $n > 8) {
                        $results[$n][$name][] = ['time_us' => null, 'mem_bytes' => null, 'peak_bytes' => null, 'correct' => null, 'note' => 'skipped'];
                        continue;
                    }

                    try {
                        $mem0 = memory_get_usage(true);
                        $peak0 = memory_get_peak_usage(true);
                        $t0 = hrtime(true);
                        $sorted = $class::sort($arr);
                        $t1 = hrtime(true);
                        $mem1 = memory_get_usage(true);
                        $peak1 = memory_get_peak_usage(true);

                        $deltaNs = $t1 - $t0;
                        $timeUs = $deltaNs / 1000.0; // microseconds
                        $memDelta = $mem1 - $mem0;
                        $peakDelta = $peak1 - $peak0;

                        // correctness check
                        $expected = $baseArray;
                        sort($expected);
                        $ok = array_values($expected) === array_values($sorted);

                        $results[$n][$name][] = [
                            'time_us' => $timeUs,
                            'mem_bytes' => $memDelta,
                            'peak_bytes' => $peakDelta,
                            'correct' => $ok,
                            'note' => null,
                        ];
                    } catch (\Throwable $e) {
                        $results[$n][$name][] = ['time_us' => null, 'mem_bytes' => null, 'peak_bytes' => null, 'correct' => false, 'note' => 'threw: ' . $e->getMessage()];
                        echo "  $name threw: " . $e->getMessage() . "\n";
                    }
                }
            }

            // compute averages and print
            foreach ($algorithms as $name => $class) {
                $entries = array_filter($results[$n][$name], function ($v) {
                    return is_array($v) && isset($v['time_us']) && is_numeric($v['time_us']);
                });
                if (count($entries) === 0) {
                    printf("  %-15s : %s\n", $name, 'n/a');
                    continue;
                }
                $times = array_column($entries, 'time_us');
                $mems = array_column($entries, 'mem_bytes');
                $peaks = array_column($entries, 'peak_bytes');
                $corrects = array_column($results[$n][$name], 'correct');

                $validTimes = array_filter($times, function ($v) { return is_numeric($v); });
                $avg = array_sum($validTimes) / count($validTimes);
                $min = min($validTimes);
                $max = max($validTimes);
                $avgMem = array_sum($mems) / count($mems);
                $avgPeak = array_sum($peaks) / count($peaks);
                $correctCount = count(array_filter($corrects, function ($v) { return $v === true; }));
                $attempts = count($results[$n][$name]);

                printf("  %-15s : avg %8.3f ms | min %8.3f ms | max %8.3f ms | mem avg %8.1f KB | correct %d/%d\n", $name, $avg / 1000.0, $min / 1000.0, $max / 1000.0, $avgMem / 1024.0, $correctCount, $attempts);
            }

            echo "\n";
        }

        $summaryPath = ROOT_DIR . '/benchmark_results.json';
        file_put_contents($summaryPath, json_encode($results, JSON_PRETTY_PRINT));
        echo "Results written to scripts/benchmark_results.json\n";

        echo "Done.\n";
    }
}
