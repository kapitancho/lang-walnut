#!/usr/bin/env php
<?php
require __DIR__ . '/inc/autoload.php';
require __DIR__ . '/inc/compiler-builder.php';

use Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper\NodeCodeMapper;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Implementation\CliRunner;

$folder = $argv[1] ?? '.';

$compiler   = buildCompilerFromNutcfg()->withCodeMapper(new NodeCodeMapper());
$sourceRoot = sourceRootFromNutcfg();
$root       = $sourceRoot . '/';

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . $folder)) as $file) {
    $pathname = $file->getPathname();
    if (str_ends_with($pathname, '.test.nut')) {
        $source = substr($pathname, strlen($root), -9) . '-test';
        echo PHP_EOL, "Executing ", $source, PHP_EOL;
        try {
            $cliRunner = new CliRunner($compiler->withStartModule($source));
            $result = $cliRunner->run([]);
            if ($result instanceof CompilationFailure) {
                foreach ($result->errors as $error) {
                    echo $error->errorMessage, ' @ ', $error->sourceLocations[0]->moduleName, ':',
                        $error->sourceLocations[0]->startPosition, "\n";
                }
            } else {
                echo $result->returnValue;
            }
        } catch (Exception $exception) {
            echo "\033[0;34m", $exception->getMessage(), "\033[0m", PHP_EOL;
        } catch (Throwable $exception) {
            echo $exception->getMessage(), PHP_EOL;
        }
    }
}
