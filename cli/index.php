<?php
require_once __DIR__ . '/../vendor/autoload.php';

$input = $argv;
array_shift($input);
$source = array_shift($input);

if (!isset($source)) {
	echo "Usage: php index.php <source> [<args>]\n";
	exit(1);
}

$content = (require __DIR__ . '/factory.inc.php')->entryPoint->call($source, ... $input);
echo $content, PHP_EOL;