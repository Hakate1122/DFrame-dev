<?php
namespace DFrame\Utils\Sorting;

/**
 * **Utility: Sorting - TimSort**
 * 
 * Implements the TimSort algorithm to sort an array.
 */
class TimSort
{
	public static function sort(array $arr): array
	{
		$n = count($arr);
		if ($n <= 1) return $arr;

		$minRun = self::minRunLength($n);

		// Identify natural runs and extend them to at least minRun using insertion sort
		$runs = [];
		$i = 0;
		while ($i < $n) {
			$start = $i;
			$i++;
			if ($i < $n && $arr[$i - 1] <= $arr[$i]) {
				// ascending run
				while ($i < $n && $arr[$i - 1] <= $arr[$i]) $i++;
			} else {
				// descending run
				while ($i < $n && $arr[$i - 1] > $arr[$i]) $i++;
				// reverse to make ascending
				$end = $i - 1;
				for ($l = $start, $r = $end; $l < $r; $l++, $r--) {
					$tmp = $arr[$l]; $arr[$l] = $arr[$r]; $arr[$r] = $tmp;
				}
			}

			$len = $i - $start;
			if ($len < $minRun) {
				$force = min($minRun, $n - $start);
				self::insertionSortRange($arr, $start, $start + $force - 1);
				$len = $force;
				$i = $start + $len;
			}

			$runs[] = ['start' => $start, 'len' => $len];
		}

		// Merge runs pairwise until single run remains (simple merging strategy)
		while (count($runs) > 1) {
			$newRuns = [];
			for ($r = 0; $r < count($runs); $r += 2) {
				if ($r + 1 < count($runs)) {
					$a = $runs[$r];
					$b = $runs[$r + 1];
					$merged = self::merge(
						array_slice($arr, $a['start'], $a['len']),
						array_slice($arr, $b['start'], $b['len'])
					);
					array_splice($arr, $a['start'], $a['len'] + $b['len'], $merged);
					$newRuns[] = ['start' => $a['start'], 'len' => count($merged)];
				} else {
					$newRuns[] = $runs[$r];
				}
			}
			$runs = $newRuns;
		}

		return $arr;
	}

	public static function debug(array $arr, ?callable $onStep = null, int $msDelay = 0, ?int $memoryLimitBytes = null): array
	{
		$step = 0;
		$n = count($arr);
		if ($n <= 1) return $arr;

		$minRun = self::minRunLength($n);
		$runs = [];
		$i = 0;
		while ($i < $n) {
			if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
				$status = 'memory_limit_exceeded';
				if ($onStep) {
					$onStep($arr, $step, $status);
				} else {
					echo json_encode(['status' => $status, 'step' => $step, 'array' => $arr]) . PHP_EOL;
				}
				return $arr;
			}

			$start = $i;
			$i++;
			if ($i < $n && $arr[$i - 1] <= $arr[$i]) {
				while ($i < $n && $arr[$i - 1] <= $arr[$i]) $i++;
			} else {
				while ($i < $n && $arr[$i - 1] > $arr[$i]) $i++;
				$end = $i - 1;
				for ($l = $start, $r = $end; $l < $r; $l++, $r--) {
					$tmp = $arr[$l]; $arr[$l] = $arr[$r]; $arr[$r] = $tmp;
				}
			}

			$len = $i - $start;
			if ($len < $minRun) {
				$force = min($minRun, $n - $start);
				self::insertionSortRange($arr, $start, $start + $force - 1);
				$len = $force;
				$i = $start + $len;
			}

			$runs[] = ['start' => $start, 'len' => $len];
			$step++;
			if ($onStep) {
				$onStep($arr, $step, null);
			} else {
				echo json_encode(['step' => $step, 'current' => $arr]) . PHP_EOL;
				if ($msDelay > 0) usleep($msDelay * 1000);
			}
		}

		// merge passes with debug reporting
		while (count($runs) > 1) {
			$newRuns = [];
			for ($r = 0; $r < count($runs); $r += 2) {
				if ($memoryLimitBytes !== null && memory_get_usage(true) > $memoryLimitBytes) {
					$status = 'memory_limit_exceeded';
					if ($onStep) {
						$onStep($arr, $step, $status);
					} else {
						echo json_encode(['status' => $status, 'step' => $step, 'array' => $arr]) . PHP_EOL;
					}
					return $arr;
				}

				if ($r + 1 < count($runs)) {
					$a = $runs[$r];
					$b = $runs[$r + 1];
					$merged = self::merge(
						array_slice($arr, $a['start'], $a['len']),
						array_slice($arr, $b['start'], $b['len'])
					);
					array_splice($arr, $a['start'], $a['len'] + $b['len'], $merged);
					$newRuns[] = ['start' => $a['start'], 'len' => count($merged)];
					$step++;
						if ($onStep) {
							$onStep($arr, $step, null);
						} else {
						echo json_encode(['step' => $step, 'current' => $arr]) . PHP_EOL;
						if ($msDelay > 0) usleep($msDelay * 1000);
					}
				} else {
					$newRuns[] = $runs[$r];
				}
			}
			$runs = $newRuns;
		}

		return $arr;
	}

	private static function minRunLength(int $n): int
	{
		$r = 0;
		while ($n >= 64) {
			$r |= $n & 1;
			$n >>= 1;
		}
		return $n + $r;
	}

	private static function insertionSortRange(array &$arr, int $left, int $right): void
	{
		for ($i = $left + 1; $i <= $right; $i++) {
			$temp = $arr[$i];
			$j = $i - 1;
			while ($j >= $left && $arr[$j] > $temp) {
				$arr[$j + 1] = $arr[$j];
				$j--;
			}
			$arr[$j + 1] = $temp;
		}
	}

	private static function merge(array $leftArray, array $rightArray): array
	{
		$result = [];
		$i = 0; $j = 0;
		$ln = count($leftArray); $rn = count($rightArray);
		while ($i < $ln && $j < $rn) {
			if ($leftArray[$i] <= $rightArray[$j]) {
				$result[] = $leftArray[$i++];
			} else {
				$result[] = $rightArray[$j++];
			}
		}
		while ($i < $ln) $result[] = $leftArray[$i++];
		while ($j < $rn) $result[] = $rightArray[$j++];
		return $result;
	}
}