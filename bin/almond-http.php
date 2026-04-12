#!/usr/bin/env php
<?php
require __DIR__ . '/inc/autoload.php';

$host   = '0.0.0.0';
$port   = 8080;
$module = 'server';

$args = array_slice($argv, 1);
while ($arg = array_shift($args)) {
    match (true) {
        $arg === '--host'   || $arg === '-h' => $host   = array_shift($args) ?? $host,
        $arg === '--port'   || $arg === '-p' => $port   = (int) (array_shift($args) ?? $port),
        $arg === '--module' || $arg === '-m' => $module = array_shift($args) ?? $module,
        $arg === '--help' => (function () use ($host, $port) {
            echo <<<HELP
Walnut HTTP Server

Usage: walnut serve [options]

Options:
  --host, -h <host>     Host to bind to (default: 0.0.0.0)
  --port, -p <port>     Port to listen on (default: 8080)
  --module, -m <name>   Walnut module to serve (default: server)
  --help                Show this help

HELP;
            exit(0);
        })(),
        default => null,
    };
}

// Locate the router. When running from a PHAR the router must be a physical
// file (php -S does not accept phar:// paths), so we copy it to a temp file
// and pass the PHAR path via an env variable so the router can bootstrap itself.
$pharPath = Phar::running(false);

if ($pharPath) {
    $routerFile = tempnam(sys_get_temp_dir(), 'walnut-router-') . '.php';
    copy('phar://' . $pharPath . '/http/almond-router.php', $routerFile);
    register_shutdown_function(static fn() => @unlink($routerFile));
} else {
    $routerFile = realpath(__DIR__ . '/../http/almond-router.php');
}

echo "Walnut HTTP Server\n";
echo "  Host:   $host\n";
echo "  Port:   $port\n";
echo "  Module: $module\n";
echo "  Press Ctrl+C to stop\n\n";

$env = sprintf(
    'WALNUT_PHAR_PATH=%s WALNUT_HTTP_MODULE=%s',
    escapeshellarg($pharPath ?: ''),
    escapeshellarg($module)
);
$cmd = sprintf(
    '%s php -S %s:%d %s',
    $env,
    escapeshellarg($host),
    $port,
    escapeshellarg($routerFile)
);

passthru($cmd, $exitCode);
exit($exitCode);
