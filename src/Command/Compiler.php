<?php

namespace DFrame\Command;

/**
 * Compiler tools for various resources.
 * 
 * Currently supports TypeScript compilation.
 */
class Compiler
{
    /**
     * Compile TypeScript files to JavaScript.
     *
     * - If $path is null, compiles all `.ts` files in `resource/ts/`.
     * - If $path is a directory, compiles all `.ts` files in that directory.
     * - If $path is a file, compiles that single file.
     *
     * $target is the TypeScript target (ES5, ES2015, ES2017, ESNext, ...).
     */
    public static function compileTS(?string $path = null, string $target = 'ES5')
    {
        return function ($argv) use ($path, $target) {
            $argPath = $argv[2] ?? $path;
            $argTarget = $argv[3] ?? $target;
            $baseResourceTs = rtrim(ROOT_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'ts';

            $candidates = [];

            if ($argPath === null || $argPath === '') {
                $candidates = glob($baseResourceTs . DIRECTORY_SEPARATOR . '*.ts') ?: [];
            } else {
                // Normalize incoming path: allow paths relative to project root or resource/ts
                $resolved = $argPath;

                if (!file_exists($resolved)) {
                    $try = rtrim(ROOT_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($argPath, "\/\\");
                    if (file_exists($try)) {
                        $resolved = $try;
                    } else {
                        $try2 = $baseResourceTs . DIRECTORY_SEPARATOR . ltrim($argPath, "\/\\");
                        if (file_exists($try2)) {
                            $resolved = $try2;
                        }
                    }
                }

                if (is_dir($resolved)) {
                    $candidates = glob(rtrim($resolved, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.ts') ?: [];
                } elseif (is_file($resolved)) {
                    $candidates = [$resolved];
                } else {
                    echo "File not found: " . cli_red((string)$argPath) . "\n";
                    return;
                }
            }

            if (empty($candidates)) {
                echo "No TypeScript files to compile.\n";
                return;
            }

            $outDir = rtrim(ROOT_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'js';
            if (!is_dir($outDir)) {
                @mkdir($outDir, 0755, true);
            }

            foreach ($candidates as $file) {
                $file = realpath($file) ?: $file;

                $command = 'tsc ' . escapeshellarg($file) . ' --target ' . escapeshellarg($argTarget) . ' --outDir ' . escapeshellarg($outDir);

                exec($command . ' 2>&1', $output, $returnVar);

                $relative = str_replace(rtrim(ROOT_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '', $file);
                if ($returnVar === 0) {
                    echo "Compiled " . cli_green($relative) . " -> " . cli_green($outDir) . "\n";
                } else {
                    echo "Failed to compile " . cli_red($relative) . " (target=" . cli_red($target) . ")\n";
                    foreach ($output as $line) {
                        echo $line . "\n";
                    }
                }
            }
        };
    }
}