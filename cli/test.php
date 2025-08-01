<?php

use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;

require_once __DIR__ . '/../vendor/autoload.php';

$folder = $argv[1] ?? '.';

/** @var string $sourceRoot */
$epBuilder = (require __DIR__ . '/factory.inc.php')->entryPointBuilder;

$root = $sourceRoot . '/';
if (!file_exists($root . $folder)) {
	echo "Folder $folder does not exist in $sourceRoot" . PHP_EOL;
	exit(1);
}
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . $folder)) as $file) {
	$pathname = $file->getPathname();
	if (str_ends_with($pathname, '.test.nut')) {
		$source = substr($pathname, strlen($root), -9) . '-test';
		echo PHP_EOL, "Executing ", $source, PHP_EOL;
		try {
			echo $epBuilder->build($source)->call(), PHP_EOL;
		} catch (AnalyserException|ParserException $analyserException) {
			echo "\033[0;34m", $analyserException->getMessage(), "\033[0m", PHP_EOL;
		} catch (Throwable $exception) {
			echo $exception->getMessage(), PHP_EOL;
			//echo $exception->getTraceAsString(), PHP_EOL;
		}
	}
}
