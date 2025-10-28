<?php

// Default configuration
$host = '0.0.0.0';
$port = 8084;  // RoadRunner default port
$docroot = getcwd();

// Parse command line arguments
$args = $argv;
array_shift($args); // Remove script name

$showHelp = false;

while ($arg = array_shift($args)) {
	if ($arg === '--host' || $arg === '-h') {
		$host = array_shift($args) ?? $host;
	} elseif ($arg === '--port' || $arg === '-p') {
		$port = (int)(array_shift($args) ?? $port);
	} elseif ($arg === '--docroot' || $arg === '-d') {
		$docroot = array_shift($args) ?? $docroot;
	} elseif ($arg === '--help') {
		$showHelp = true;
	} else {
		echo "Unknown option: $arg\n";
		$showHelp = true;
		break;
	}
}

if ($showHelp) {
	echo <<<HELP
Walnut HTTP Server

Usage: walnut serve [options]

Options:
  --host, -h <host>       Host to bind to (default: 0.0.0.0)
  --port, -p <port>       Port to listen on (default: 8084)
  --docroot, -d <path>    Document root directory (default: current directory)
  --help                  Show this help message

Configuration:
  The server looks for nutcfg.json in the document root.
  You can specify:
    - sourceRoot: Root directory for Walnut modules
    - packages: Package mappings
    - httpModule: Entry point module name (default: "server")

Example nutcfg.json:
{
  "sourceRoot": "./walnut-src",
  "packages": {
    "core": "./core-nut-lib"
  },
  "httpModule": "server"
}

Example:
  walnut serve --port 3000
  walnut serve --host localhost --port 8080 --docroot /path/to/project

HELP;
	exit($showHelp && !isset($arg) ? 0 : 1);
}

// Determine router file path
$isInPhar = str_starts_with(__FILE__, 'phar://');
if ($isInPhar && !is_dir($docroot)) {
	$docroot = getcwd();
}
$router = __DIR__ . '/http-router.php';

// Validate router file exists
if (!file_exists($router)) {
	echo "Error: Router file not found at: $router\n";
	exit(1);
}

// Change to document root
if (!chdir($docroot)) {
	echo "Error: Could not change to document root: $docroot\n";
	exit(1);
}

echo "Walnut HTTP Server\n";
echo "==================\n";
echo "Host:     $host\n";
echo "Port:     $port\n";
echo "DocRoot:  $docroot\n";
echo "Router:   $router\n";
echo "\n";
echo "Server starting...\n";
echo "Press Ctrl+C to stop\n";
echo "\n";

// Start PHP built-in server
$command = sprintf(
	'php -S %s:%d %s',
	escapeshellarg($host),
	$port,
	escapeshellarg($router)
);

passthru($command, $exitCode);
exit($exitCode);
