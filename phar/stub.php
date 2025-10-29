#!/usr/bin/env php
<?php

// Determine version from git tags or fallback
$version = 'unknown';

// Try to read version from a generated VERSION file (created during build)
$versionFile = 'phar://' . __FILE__ . '/VERSION';
if (file_exists($versionFile)) {
    $version = trim(file_get_contents($versionFile));
}

// Fallback: try git describe if in development
if ($version === 'unknown' && function_exists('shell_exec')) {
    $gitVersion = @shell_exec('git describe --tags --always 2>/dev/null');
    if ($gitVersion) {
        $version = trim(str_replace('v', '', $gitVersion));
    }
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
        require 'phar://' . $phar_path . '/bin/walnut-test';
        break;

    case 'serve':
        require 'phar://' . $phar_path . '/bin/walnut-http';
        break;

    default:
        require 'phar://' . $phar_path . '/bin/walnut-cli';
        break;
}

__HALT_COMPILER();