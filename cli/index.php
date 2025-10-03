<?php

use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Program\InvalidEntryPointDependency;

require_once __DIR__ . '/../vendor/autoload.php';

$input = $argv;
array_shift($input);
$source = array_shift($input);

if (!isset($source)) {
	echo "Usage: php index.php <source> [<args>]\n";
	exit(1);
}

try {
	$content = (require __DIR__ . '/factory.inc.php')->entryPoint->call($source, ... $input);
	echo $content, PHP_EOL;
} catch (ModuleDependencyException $m) {
	echo "Error: ", $m->getMessage(), PHP_EOL;
	exit(1);
} catch (InvalidEntryPointDependency $d) {
	echo "Error: ", $d->getMessage(), PHP_EOL;
	exit(1);
}