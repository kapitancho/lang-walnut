#!/usr/bin/env php
<?php
require __DIR__ . '/inc/autoload.php';

use Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper\NodeCodeMapper;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Implementation\Compiler;
use Walnut\Lang\Almond\Source\Implementation\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\PackageBasedSourceFinder;

// Parse arguments
$showErrorsOnly = false;
$folder = '.';

for ($i = 1; $i < count($argv); $i++) {
    if ($argv[$i] === '--show-errors-only') {
        $showErrorsOnly = true;
    } elseif (!str_starts_with($argv[$i], '--')) {
        $folder = $argv[$i];
    }
}

$sourceRoot = __DIR__ . '/../almond/walnut-src';
$root = $sourceRoot . '/';

if (!file_exists($root . $folder)) {
    echo "Folder $folder does not exist in $sourceRoot" . PHP_EOL;
    exit(1);
}

$compiler = Compiler::builder()
    ->withCodeMapper(new NodeCodeMapper())
    ->withAddedSourceFinder(
        new PackageBasedSourceFinder(
            new PackageConfiguration(
                $sourceRoot,
                [
                    'core' => __DIR__ . '/../almond/core-nut-lib',
                ]
            )
        )
    );

$errorCount = 0;
$fileCount = 0;

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . $folder)) as $file) {
    $pathname = $file->getPathname();
    if (str_ends_with($pathname, '.nut') && !str_ends_with($pathname, '.test.nut')) {
        $fileCount++;
        $source = substr($pathname, strlen($root), -4);
        if (!$showErrorsOnly) {
            echo PHP_EOL, "Analysing ", $source, PHP_EOL;
        }

        try {
            $result = $compiler->withStartModule($source)->compile();
        } catch (Throwable $e) {
            $errorCount++;
            if ($showErrorsOnly) {
                echo PHP_EOL, "Analysing ", $source, PHP_EOL;
            }
            echo "\033[0;31m✗ EXCEPTION\033[0m ", $e->getMessage(), PHP_EOL;
            continue;
        }

        if ($result instanceof CompilationFailure) {
            $errorCount++;
            if ($showErrorsOnly) {
                echo PHP_EOL, "Analysing ", $source, PHP_EOL;
            }
            foreach ($result->errors as $error) {
                echo "\033[0;31m✗ ERROR\033[0m ", $error->errorMessage, PHP_EOL;
            }
        } else {
            if (!$showErrorsOnly) {
                echo "\033[0;32m✓ OK\033[0m", PHP_EOL;
            }
        }
    }
}

echo PHP_EOL, "Analysis complete: $fileCount file(s) checked, $errorCount error(s)" . PHP_EOL;
exit($errorCount > 0 ? 1 : 0);
