<?php

use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Implementation\Compilation\CompilerFactory;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\CallbackSourceFinder;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\CompositeSourceFinder;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\PackageBasedSourceFinder;
use Walnut\Lang\Implementation\Program\EntryPoint\Cli\CliEntryPoint;
use Walnut\Lang\Implementation\Program\EntryPoint\Cli\CliEntryPointBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

$input = $argv;
array_shift($input);
$source = array_shift($input);

if (!isset($source)) {
	echo "Usage: php index.php <source> [<args>]\n";
	exit(1);
}

try {
	$cfg = @json_decode(@file_get_contents(__DIR__ . '/../nutcfg.json') ?? '{}', true);
	$sourceRoot = __DIR__ . ($cfg['sourceRoot'] ? '/../' . $cfg['sourceRoot'] : __DIR__ . '/../walnut-src');
	$packages = $cfg['packages'] ?
		array_map(fn(string $path) => __DIR__ . '/../' . $path, $cfg['packages']) :
		['core' => __DIR__ . '/../core-nut-lib'];

	$compiler = new CompilerFactory()->customCompiler(
		new CompositeSourceFinder(
			new CallbackSourceFinder([
				'stdin.nut' => fn() => file_get_contents('php://stdin')
			]),
			new PackageBasedSourceFinder(
				$sourceRoot,
				$packages
			)
		)
	);
	$ep = new CliEntryPoint(new CliEntryPointBuilder($compiler));
	$content = $ep->call($source, ... $input);
	echo $content, PHP_EOL;
} catch (ModuleDependencyException $m) {
	echo "Error: ", $m->getMessage(), PHP_EOL;
	exit(1);
} catch (InvalidEntryPointDependency $d) {
	echo "Error: ", $d->getMessage(), PHP_EOL;
	exit(1);
}