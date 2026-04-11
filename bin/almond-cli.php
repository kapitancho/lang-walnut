#!/usr/bin/env php
<?php
require __DIR__ . '/inc/autoload.php';
require __DIR__ . '/inc/compiler-builder.php';

use Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper\NodeCodeMapper;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Implementation\CliRunner;

$input = $argv;
array_shift($input);
$source = array_shift($input);

if (!isset($source)) {
    echo "Usage: almond-cli <source> [<args>]\n";
    exit(1);
}

$compiler = buildCompilerFromNutcfg()
    ->withStartModule($source)
    ->withCodeMapper(new NodeCodeMapper());

$cliRunner = new CliRunner($compiler);
$result = $cliRunner->run($input);
if ($result instanceof CompilationFailure) {
    foreach ($result->errors as $error) {
        echo $error->errorMessage, "\n";
    }
} else {
    echo $result->returnValue;
}
