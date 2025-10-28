#!/usr/bin/env php
<?php

// Read version from composer.json in phar
$composerPath = 'phar://' . __FILE__ . '/composer.json';
$version = 'unknown';
if (file_exists($composerPath)) {
    $composer = json_decode(file_get_contents($composerPath), true);
    $version = $composer['version'] ?? 'unknown';
}

// Get the first argument
$firstArg = $GLOBALS['argv'][1] ?? null;

// Handle special flags
if ($firstArg === '--version' || $firstArg === '-v') {
    echo "Walnut $version\n";
    exit(0);
}

if ($firstArg === '--help' || $firstArg === '-h') {
    echo <<<HELP
Walnut $version

Usage: walnut [command] [options]

Commands:
  walnut <module> [args...]    Execute a Walnut module (default)
  walnut test [folder]         Run Walnut tests
  walnut serve [options]       Start HTTP server with RoadRunner
  walnut --version             Show version
  walnut --help                Show this help

Examples:
  walnut myapp arg1 arg2       Execute module 'myapp' with arguments
  walnut test tests/           Run tests in tests/ folder
  walnut serve --port 3000     Start server on port 3000

Options for 'serve':
  --port, -p <port>           Port to listen on (default: 8084)
  --host, -h <host>           Host to bind to (default: 0.0.0.0)
  --help                       Show server help

HELP;
    exit(0);
}

// Determine which command to run
$command = 'exec';  // Default command

if (in_array($firstArg, ['test', 'serve'])) {
    $command = $firstArg;
    array_splice($GLOBALS['argv'], 1, 1);  // Remove command from argv
}

$phar_path = __FILE__;

// Route to appropriate entry point
switch ($command) {
    case 'test':
        require 'phar://' . $phar_path . '/cli/test.php';
        break;

    case 'serve':
        require 'phar://' . $phar_path . '/http/http.php';
        break;

    case 'exec':
    default:
        require 'phar://' . $phar_path . '/cli/index.php';
        break;
}

__HALT_COMPILER();