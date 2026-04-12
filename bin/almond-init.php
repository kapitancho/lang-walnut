#!/usr/bin/env php
<?php
require __DIR__ . '/inc/autoload.php';

if (file_exists(getcwd() . '/nutcfg.json')) {
    echo "nutcfg.json already exists — project is already initialised.\n";
    exit(1);
}

// Detect which scenario we're running in, to determine how to reference core-nut-lib.
$pharPath = Phar::running(false);

if ($pharPath) {
    // Scenario 3: running from PHAR — extract core-nut-lib into the project dir.
    echo "Extracting core-nut-lib from PHAR...\n";
    copyDirectory('phar://' . $pharPath . '/almond/core-nut-lib', getcwd() . '/core-nut-lib');
    $coreRef = './core-nut-lib';
} elseif (is_dir(getcwd() . '/vendor/walnut/lang/almond/core-nut-lib')) {
    // Scenario 2: Composer install — reference the installed library; no copy needed.
    $coreRef = './vendor/walnut/lang/almond/core-nut-lib';
} else {
    // Scenario 1: source clone — copy from the repo's almond/ directory.
    $srcCoreLib = realpath(__DIR__ . '/../almond/core-nut-lib');
    if (!$srcCoreLib || !is_dir($srcCoreLib)) {
        echo "Cannot locate core-nut-lib. Run from the project root after 'composer install'.\n";
        exit(1);
    }
    echo "Copying core-nut-lib...\n";
    copyDirectory($srcCoreLib, getcwd() . '/core-nut-lib');
    $coreRef = './core-nut-lib';
}

// Create the source directory if it doesn't exist.
$srcDir = getcwd() . '/walnut-src';
if (!is_dir($srcDir)) {
    mkdir($srcDir, 0755, true);
}

// Write nutcfg.json.
$nutcfg = json_encode(
    ['sourceRoot' => './walnut-src', 'packages' => ['core' => $coreRef]],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . "\n";
file_put_contents(getcwd() . '/nutcfg.json', $nutcfg);

echo "Project initialised:\n";
echo "  walnut-src/  — add your .nut source files here\n";
echo "  nutcfg.json  — project configuration\n";
if ($pharPath || !str_starts_with($coreRef, './vendor/')) {
    echo "  core-nut-lib/ — Walnut standard library\n";
}

// ---------------------------------------------------------------------------

function copyDirectory(string $src, string $dest): void {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    $srcLen = strlen(rtrim($src, '/'));
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iter as $item) {
        // Strip the source prefix to get a relative path.
        $relative = ltrim(substr((string) $item->getPathname(), $srcLen), '/\\');
        $target = $dest . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            copy((string) $item->getPathname(), $target);
        }
    }
}
