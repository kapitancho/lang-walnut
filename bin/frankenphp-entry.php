#!/usr/bin/env php
<?php
// FrankenPHP embedded entry point.
// Equivalent of phar/stub.php for the native binary scenario (Scenario 4).
// Files are accessible via __DIR__ on FrankenPHP's virtual filesystem.

$version = 'unknown';
$versionFile = dirname(__DIR__) . '/VERSION';
if (file_exists($versionFile)) {
    $version = trim((string) file_get_contents($versionFile));
}

$firstArg = $GLOBALS['argv'][1] ?? null;

if ($firstArg === '--version' || $firstArg === '-v') {
    echo "Walnut $version\n";
    exit(0);
}

if ($firstArg === '--help' || $firstArg === '-h') {
    echo <<<HELP
Walnut $version

Usage: walnut <command> [options]

Commands:
  init                     Initialise a new Walnut project in the current directory
  cli <module> [args...]   Execute a Walnut module
  test [folder]            Run .test.nut test files
  analyse [folder]         Type-check all .nut modules
  serve [options]          Start the HTTP server
  lsp                      Start the Language Server (used by VS Code)
  --version                Print version
  --help                   Print this help

  When the first argument is not a recognised command, it is treated as a
  module name and passed directly to 'cli':
    walnut mymodule arg1 arg2   ≡   walnut cli mymodule arg1 arg2

Examples:
  walnut init
  walnut cli main
  walnut test
  walnut serve --port 3000
  walnut analyse --show-errors-only

HELP;
    exit(0);
}

$subcommands = ['init', 'cli', 'test', 'analyse', 'serve', 'lsp'];

$command = 'cli'; // default — treat first arg as a module name
if (in_array($firstArg, $subcommands, true)) {
    $command = $firstArg;
    array_splice($GLOBALS['argv'], 1, 1);
}

switch ($command) {
    case 'init':
        require __DIR__ . '/almond-init.php';
        break;
    case 'test':
        require __DIR__ . '/almond-test.php';
        break;
    case 'analyse':
        require __DIR__ . '/almond-analyse.php';
        break;
    case 'serve':
        require __DIR__ . '/almond-http.php';
        break;
    case 'lsp':
        require __DIR__ . '/almond-lsp.php';
        break;
    case 'cli':
    default:
        require __DIR__ . '/almond-cli.php';
        break;
}
