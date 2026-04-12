<?php

/**
 * Router script for PHP's built-in development server.
 *
 * Launched by bin/almond-http.php via:
 *   WALNUT_HTTP_MODULE=<module> php -S host:port http/almond-router.php
 *
 * When running from a PHAR, bin/almond-http.php sets WALNUT_PHAR_PATH so this
 * script can load the autoloader from inside the archive.
 */

declare(strict_types=1);

$pharPath = getenv('WALNUT_PHAR_PATH') ?: '';
if ($pharPath) {
    require_once 'phar://' . $pharPath . '/vendor/autoload.php';
    require_once 'phar://' . $pharPath . '/bin/inc/compiler-builder.php';
} else {
    require_once __DIR__ . '/../bin/inc/autoload.php';
    require_once __DIR__ . '/../bin/inc/compiler-builder.php';
}

use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpRequestMethod;
use Walnut\Lang\Almond\Runner\Implementation\Http\Message\HttpRequest;
use Walnut\Lang\Almond\Runner\Implementation\HttpRunner;

$httpModule = getenv('WALNUT_HTTP_MODULE') ?: 'server';

$compiler = buildCompilerFromNutcfg()->withStartModule($httpModule);
$runner   = new HttpRunner($compiler);

// Build the request from PHP superglobals.
$protocolVersion = HttpProtocolVersion::tryFrom(
    $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1'
) ?? HttpProtocolVersion::http_1_1;

$method = HttpRequestMethod::from($_SERVER['REQUEST_METHOD'] ?? 'GET');
$target = $_SERVER['REQUEST_URI'] ?? '/';

$headers = [];
foreach ($_SERVER as $key => $value) {
    if (str_starts_with($key, 'HTTP_')) {
        $headers[strtolower(str_replace('_', '-', substr($key, 5)))] = [$value];
    }
}
if (isset($_SERVER['CONTENT_TYPE']))  { $headers['content-type']   = [$_SERVER['CONTENT_TYPE']]; }
if (isset($_SERVER['CONTENT_LENGTH'])) { $headers['content-length'] = [$_SERVER['CONTENT_LENGTH']]; }

$body   = file_get_contents('php://input') ?: null;
$result = $runner->run(new HttpRequest($protocolVersion, $method, $target, $headers, $body));

if ($result instanceof CompilationFailure) {
    http_response_code(500);
    header('Content-Type: text/plain');
    foreach ($result->errors as $error) {
        echo $error->errorMessage, "\n";
    }
    return;
}

$response = $result->returnValue;
http_response_code($response->statusCode);
foreach ($response->headers as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
if ($response->body !== null) {
    echo $response->body;
}
