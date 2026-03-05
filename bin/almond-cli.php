#!/usr/bin/env php
<?php
require __DIR__ . '/inc/autoload.php';

use Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper\NodeCodeMapper;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Implementation\CliRunner;
use Walnut\Lang\Almond\Runner\Implementation\Compiler;
use Walnut\Lang\Almond\Source\Implementation\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\PackageBasedSourceFinder;

$input = $argv;
array_shift($input);
$source = array_shift($input);

if (!isset($source)) {
    echo "Usage: almond-cli <source> [<args>]\n";
    exit(1);
}

$sourceRoot = __DIR__ . '/../almond/walnut-src';

$compiler = Compiler::builder()
	->withStartModule($source)
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


$cliRunner = new CliRunner($compiler);
$result = $cliRunner->run($input);
if ($result instanceof CompilationFailure) {
	foreach($result->errors as $error) {
		echo $error->errorMessage, "\n";
	}
} else {
	echo $result->returnValue;
}
