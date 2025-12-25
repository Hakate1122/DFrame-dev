<?php

/** Simple PHAR builder CLI */
class BuildPhar{
    public const VERSION = '0.0.1-dev';
}

const VERSION = BuildPhar::VERSION;

$checkPhar = extension_loaded('phar') && !ini_get('phar.readonly');

function show_helper()
{
    global $checkPhar;
    echo "PHAR Builder v" . VERSION . "\n\n";
    echo $checkPhar ? "phar extension is loaded and writable.\n" : "WARNING: phar extension is not loaded or is read-only. PHAR building will not work.\n";
    echo "\n";
    echo "Usage:\n";
    echo "  php build-phar.php help              Show this help and features\n";
    echo "  php build-phar.php build [options]   Build a PHAR\n";
    echo "  php build-phar.php self-build [options] Build a PHAR containing this builder (self)\n";
    echo "  php build-phar.php extract [options] Build or list PHAR contents\n";
    echo "\n";
    echo "Features:\n";
    echo "  - Core helper: show version and functions\n";
    echo "  - Build phar: specify output name, includes, and stub (file or text)\n";
    echo "\n";
    echo "Build options:\n";
    echo "  --output=NAME             Output PHAR file name (default: app.phar)\n";
    echo "  --include=PATHS           Comma-separated files/dirs (relative to script) to include\n";
    echo "  --stub-file=FILE          Path to stub file to use\n";
    echo "  --stub-string=STRING      Provide stub contents directly\n";
    echo "  --root-extras=FILES       Comma-separated root files to include (default: .env,cacert.pem,composer.json,composer.lock)\n";
    echo "  --force                   Overwrite existing output file without prompt\n";
    echo "  --self                    Include this builder script into the PHAR and use it as stub\n";
    echo "\n";
    echo "Examples:\n";
    echo "  php build-phar.php build --output=my.phar --include=app,config,public --stub-file=stubs/boot.php\n";
    echo "  php build-phar.php self-build --output=my.phar   # build PHAR that includes this builder as stub\n";
}

/**
 * Extract or list a PHAR's contents.
 * Options:
 *  --phar=FILE   PHAR file path (relative or absolute)
 *  --output=DIR  Destination dir (default: <pharname>-extracted)
 *  --force       Overwrite existing files
 *  --list        Only list files
 */
function extract_phar(array $opts)
{
    $baseDir = __DIR__;
    $pharArg = $opts['phar'] ?? '';
    if (trim($pharArg) === '') {
        $pharArg = prompt("PHAR file path (relative to project root): ");
        if (trim($pharArg) === '') {
            echo "Aborted.\n";
            return;
        }
    }

    // Accept either absolute/relative path or project-relative
    $pharPath = $pharArg;
    if (!is_file($pharPath)) {
        $candidate = $baseDir . DIRECTORY_SEPARATOR . $pharArg;
        if (is_file($candidate)) {
            $pharPath = $candidate;
        }
    }

    if (!is_file($pharPath)) {
        echo "ERROR: PHAR file not found: {$pharArg}\n";
        return;
    }

    $baseName = pathinfo($pharPath, PATHINFO_FILENAME);
    $dest = $opts['output'] ?? ($baseName . '-extracted');
    if (!is_dir($dest)) {
        if (!mkdir($dest, 0777, true)) {
            echo "ERROR: Failed to create destination directory: {$dest}\n";
            return;
        }
    }

    try {
        $phar = new Phar($pharPath);

        if (!empty($opts['list'])) {
            foreach ($phar as $file) {
                /** @var PharFileInfo $file */
                echo $file->getPathName() . PHP_EOL;
            }
            return;
        }

        $overwrite = !empty($opts['force']);
        echo "Extracting {$pharPath} to {$dest} (overwrite=" . ($overwrite ? 'yes' : 'no') . ")...\n";
        $phar->extractTo($dest, null, $overwrite);
        echo "Extraction complete.\n";
    } catch (Exception $e) {
        echo "Extraction failed: " . $e->getMessage() . "\n";
    }
}

function prompt($text)
{
    echo $text;
    $line = trim(fgets(STDIN));
    return $line;
}

function normalize_rel($path)
{
    return str_replace('\\', '/', ltrim($path, '/\\'));
}

function collect_files(array $includes, $baseDir)
{
    $collected = [];
    foreach ($includes as $inc) {
        $inc = trim($inc);
        if ($inc === '') {
            continue;
        }
        $full = $baseDir . DIRECTORY_SEPARATOR . $inc;
        if (is_file($full)) {
            $rel = normalize_rel(substr($full, strlen($baseDir) + 1));
            $collected[$full] = $rel;
            continue;
        }
        if (is_dir($full)) {
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($full));
            foreach ($rii as $file) {
                if ($file->isFile()) {
                    $path = $file->getPathname();
                    $rel = normalize_rel(substr($path, strlen($baseDir) + 1));
                    $collected[$path] = $rel;
                }
            }
            continue;
        }
        // Allow patterns relative to base (e.g., "app" may be matched case-insensitively)
        // if not found, skip with a message
        fwrite(STDOUT, "Warning: include path not found: {$inc}\n");
    }
    return $collected;
}

/**
 * Build the PHAR file.
 * @param array $opts Options for building the PHAR
 * @return void
 */
function build_phar(array $opts)
{
    $baseDir = __DIR__;
    $selfName = basename(__FILE__);
    $output = $opts['output'] ?? '';

    // Require the user to provide an output filename if not supplied.
    while (true) {
        if (trim($output) === '') {
            $outInput = prompt("Output filename (required, type 'q' to cancel): ");
            if (strtolower(trim($outInput)) === 'q') {
                echo "Aborted.\n";
                return;
            }
            $outInput = trim($outInput);
            if ($outInput === '') {
                echo "Filename cannot be empty.\n";
                continue;
            }
            if (strpos($outInput, '/') !== false || strpos($outInput, '\\') !== false || strpos($outInput, DIRECTORY_SEPARATOR) !== false) {
                echo "Do not include directory separators. Enter only a filename.\n";
                continue;
            }
            if (pathinfo($outInput, PATHINFO_EXTENSION) !== 'phar') {
                $outInput .= '.phar';
            }
            $output = $outInput;
        }

        $outputPath = $baseDir . DIRECTORY_SEPARATOR . $output;

        // If file doesn't exist or user forced overwrite, proceed.
        if (!file_exists($outputPath) || !empty($opts['force'])) {
            if (file_exists($outputPath) && !empty($opts['force'])) {
                @unlink($outputPath);
            }
            break;
        }

        // File exists and not forced: ask to overwrite.
        $ans = prompt("Output {$output} exists. Overwrite? (y/N): ");
        if (strtolower($ans) === 'y') {
            @unlink($outputPath);
            break;
        }

        // User declined overwrite — ask for a new filename (loop will repeat)
        echo "Provide an alternative output filename (or type 'q' to cancel).\n";
        $output = '';
    }

    if (ini_get('phar.readonly')) {
        echo "ERROR: phar.readonly is enabled in php.ini — disable it to build PHAR files.\n";
        return;
    }

    // If building only self, do not include directories or other files
    $includes = $opts['include'] ?? [];
    if (!empty($opts['self'])) {
        $includes = [];
        echo "Self-build requested: only including {$selfName}\n";
        // Do not collect other files when building a self PHAR — only add the builder script.
        $files = [];
        $rootExtras = [$selfName];
    } else {
        if (empty($includes)) {
            $default = ['app', 'config', 'public', 'resource', 'src', 'vendor', 'dli'];
            echo "No includes specified. Default candidate folders: " . implode(', ', $default) . "\n";
            $line = prompt("Enter comma-separated includes, or press Enter to use defaults: ");
            $includes = $line === '' ? $default : array_map('trim', explode(',', $line));
        }

        $files = collect_files($includes, $baseDir);
        $rootExtras = $opts['root-extras'] ?? ['.env', 'cacert.pem', 'composer.json', 'composer.lock'];
    }
    $rootAdd = [];
    foreach ($rootExtras as $fname) {
        $fpath = $baseDir . DIRECTORY_SEPARATOR . $fname;
        if (file_exists($fpath) && is_file($fpath)) {
            $rootAdd[$fpath] = $fname;
        }
    }

    try {
        $phar = new Phar($outputPath);
        $phar->startBuffering();

        // Merge files and root extras, avoiding duplicate internal paths.
        $addMap = [];
        foreach ($files as $full => $rel) {
            $addMap[$rel] = $full;
        }
        foreach ($rootAdd as $full => $rel) {
            if (isset($addMap[$rel])) {
                // same internal path already scheduled, skip and warn
                echo "Warning: skipping duplicate entry for {$rel}\n";
                continue;
            }
            $addMap[$rel] = $full;
        }

        echo "Adding files (" . count($addMap) . ")...\n";
        foreach ($addMap as $rel => $full) {
            echo "[ADD] " . $rel . "\n";
            $phar->addFile($full, $rel);
        }

        // Stub handling
        $stub = null;
        $stubFileRel = null;
        if (!empty($opts['stub-file']) && is_file($baseDir . DIRECTORY_SEPARATOR . $opts['stub-file'])) {
            $stubFileRel = normalize_rel($opts['stub-file']);
            $stub = file_get_contents($baseDir . DIRECTORY_SEPARATOR . $opts['stub-file']);
            echo "Using stub file: " . $opts['stub-file'] . "\n";
        } elseif (!empty($opts['stub-string'])) {
            $stub = $opts['stub-string'];
            echo "Using provided stub string.\n";
        }

        // If requested, include this builder script itself and use it as the stub
        if (!empty($opts['self'])) {
            $selfName = basename(__FILE__);
            $selfPath = $baseDir . DIRECTORY_SEPARATOR . $selfName;
            if (is_file($selfPath)) {
                // ensure it's added at the PHAR root (rootAdd already contains it for self-build)
                $stubFileRel = normalize_rel($selfName);
                $stub = file_get_contents($selfPath);
                echo "Including self as stub: {$selfName}\n";
            } else {
                echo "Warning: could not locate builder script to include as self.\n";
            }
        }

        if ($stub === null) {
            // No default stub any more — require the user to pick a stub file
            // or enter stub contents interactively.
            echo "No stub specified. You must choose a stub file or enter stub text.\n";
            while (true) {
                echo "Choose stub source: (1) stub file, (2) enter stub text, (q) cancel: ";
                $choice = trim(fgets(STDIN));
                if (strtolower($choice) === 'q') {
                    echo "Aborted.\n";
                    return;
                }
                if ($choice === '1') {
                    $stubPath = prompt("Stub file path (relative to project root): ");
                    if ($stubPath === '') {
                        echo "Empty path provided.\n";
                        continue;
                    }
                    $fullStub = $baseDir . DIRECTORY_SEPARATOR . $stubPath;
                    if (!is_file($fullStub)) {
                        echo "Stub file not found: {$stubPath}\n";
                        continue;
                    }
                    $stub = file_get_contents($fullStub);
                    echo "Using stub file: {$stubPath}\n";
                    break;
                } elseif ($choice === '2') {
                    echo "Enter stub contents. End with a line containing only END\n";
                    $lines = [];
                    while (($line = fgets(STDIN)) !== false) {
                        $line = rtrim($line, "\r\n");
                        if ($line === 'END') {
                            break;
                        }
                        $lines[] = $line;
                    }
                    $stub = implode("\n", $lines);
                    echo "Using provided stub text.\n";
                    break;
                } else {
                    echo "Invalid choice. Enter 1, 2, or q.\n";
                }
            }
        }

        // If selected stub came from an external file, store its relative path for wrapping
        if (isset($stubPath) && !empty($stubPath)) {
            $stubFileRel = normalize_rel($stubPath);
        }

        // If the stub does not contain __HALT_COMPILER(), we can wrap it (when we have a stub file inside the PHAR)
        if (strpos($stub, '__HALT_COMPILER') === false) {
            if ($stubFileRel !== null) {
                $wrapper = "#!/usr/bin/env php\r\n";
                $wrapper .= "<?php Phar::mapPhar('{$output}'); require 'phar://{$output}/{$stubFileRel}'; __HALT_COMPILER();";
                $stub = $wrapper;
                echo "Wrapped stub to require phar://{$output}/{$stubFileRel} and appended __HALT_COMPILER().\n";
            } else {
                echo "ERROR: provided stub text does not contain __HALT_COMPILER(); and no stub file available to wrap.\n";
                return;
            }
        }

        $phar->setStub($stub);
        $phar->stopBuffering();

        echo "PHAR built successfully: {$outputPath}\n";
    } catch (Exception $e) {
        echo "PHAR build failed: " . $e->getMessage() . "\n";
    }
}

// Simple argv parsing for our small CLI
$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];

$cmd = $argv[1] ?? 'help';
if (in_array($cmd, ['-h', '--help', 'help'])) {
    show_helper();
    exit(0);
}

if ($cmd === 'build') {
    $opts = [];
    // parse remaining args
    for ($i = 2; $i < $argc; $i++) {
        $arg = $argv[$i];
        if (strpos($arg, '--') === 0) {
            $pair = substr($arg, 2);
            $parts = explode('=', $pair, 2);
            $key = $parts[0];
            $val = $parts[1] ?? '';
            switch ($key) {
                case 'output':
                    $opts['output'] = $val;
                    break;
                case 'include':
                    $opts['include'] = array_map('trim', explode(',', $val));
                    break;
                case 'self':
                    $opts['self'] = true;
                    break;
                case 'stub-file':
                    $opts['stub-file'] = $val;
                    break;
                case 'stub-string':
                    $opts['stub-string'] = $val;
                    break;
                case 'root-extras':
                    $opts['root-extras'] = array_filter(array_map('trim', explode(',', $val)));
                    break;
                case 'force':
                    $opts['force'] = true;
                    break;
                default:
                    echo "Unknown option: --{$key}\n";
            }
        }
    }

    build_phar($opts);
    // mark TODO step complete
    // (the manage_todo_list tool has already been used to create the plan)
    exit(0);
}

if ($cmd === 'self-build') {
    $opts = [];
    // parse remaining args (same options as build, but force self=true)
    for ($i = 2; $i < $argc; $i++) {
        $arg = $argv[$i];
        if (strpos($arg, '--') === 0) {
            $pair = substr($arg, 2);
            $parts = explode('=', $pair, 2);
            $key = $parts[0];
            $val = $parts[1] ?? '';
            switch ($key) {
                case 'output':
                    $opts['output'] = $val;
                    break;
                case 'include':
                    $opts['include'] = array_map('trim', explode(',', $val));
                    break;
                case 'stub-file':
                    $opts['stub-file'] = $val;
                    break;
                case 'stub-string':
                    $opts['stub-string'] = $val;
                    break;
                case 'root-extras':
                    $opts['root-extras'] = array_filter(array_map('trim', explode(',', $val)));
                    break;
                case 'force':
                    $opts['force'] = true;
                    break;
                default:
                    echo "Unknown option: --{$key}\n";
            }
        }
    }
    // Always enable self inclusion for this command
    $opts['self'] = true;
    build_phar($opts);
    exit(0);
}

if ($cmd === 'extract') {
    $opts = [];
    for ($i = 2; $i < $argc; $i++) {
        $arg = $argv[$i];
        if (strpos($arg, '--') === 0) {
            $pair = substr($arg, 2);
            $parts = explode('=', $pair, 2);
            $key = $parts[0];
            $val = $parts[1] ?? '';
            switch ($key) {
                case 'phar':
                    $opts['phar'] = $val;
                    break;
                case 'output':
                    $opts['output'] = $val;
                    break;
                case 'force':
                    $opts['force'] = true;
                    break;
                case 'list':
                    $opts['list'] = true;
                    break;
                default:
                    echo "Unknown option: --{$key}\n";
            }
        }
    }

    extract_phar($opts);
    exit(0);
}

// fallback
show_helper();
