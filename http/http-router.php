<?php

use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequestMethod;
use Walnut\Lang\Blueprint\Program\InvalidEntryPointDependency;
use Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration\JsonFilePackageConfigurationProvider;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\HttpEntryPointFactory;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Message\HttpRequest;

// Load configuration
$httpModule = 'server';

$factory = new HttpEntryPointFactory(
	new JsonFilePackageConfigurationProvider('nutcfg.json')
);

try {
	// Parse protocol version
	$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
	$protocolVersion = match($protocol) {
		'HTTP/1.0' => HttpProtocolVersion::http_1_0,
		'HTTP/1.1' => HttpProtocolVersion::http_1_1,
		'HTTP/2.0', 'HTTP/2' => HttpProtocolVersion::http_2,
		default => HttpProtocolVersion::http_1_1,
	};

	// Parse request method
	$methodString = $_SERVER['REQUEST_METHOD'] ?? 'GET';
	$method = HttpRequestMethod::from($methodString);

	// Get request target (URI)
	$target = $_SERVER['REQUEST_URI'] ?? '/';

	// Parse headers
	$headers = [];
	foreach ($_SERVER as $key => $value) {
		if (str_starts_with($key, 'HTTP_')) {
			$headerName = str_replace('_', '-', substr($key, 5));
			$headerName = strtolower($headerName);
			$headers[$headerName] = [$value];
		}
	}

	// Add content-type and content-length if present
	if (isset($_SERVER['CONTENT_TYPE'])) {
		$headers['content-type'] = [$_SERVER['CONTENT_TYPE']];
	}
	if (isset($_SERVER['CONTENT_LENGTH'])) {
		$headers['content-length'] = [$_SERVER['CONTENT_LENGTH']];
	}

	// Get request body
	$body = file_get_contents('php://input') ?: null;

	// Create HTTP request
	$request = new HttpRequest(
		$protocolVersion,
		$method,
		$target,
		$headers,
		$body
	);

	// Call the Walnut HTTP entry point
	$response = $factory->entryPoint->call($httpModule, $request);

	// Send response
	http_response_code($response->statusCode);

	foreach ($response->headers as $name => $values) {
		foreach ($values as $value) {
			header("$name: $value", false);
		}
	}

	if ($response->body !== null) {
		echo $response->body;
	}

} catch (ModuleDependencyException $m) {
	http_response_code(500);
	header('Content-Type: text/plain');
	echo "Module Error: ", $m->getMessage(), PHP_EOL;
} catch (InvalidEntryPointDependency $d) {
	http_response_code(500);
	header('Content-Type: text/plain');
	echo "Entry Point Error: ", $d->getMessage(), PHP_EOL;
} catch (Throwable $e) {
	http_response_code(500);
	header('Content-Type: text/plain');
	echo "Server Error: ", $e->getMessage(), PHP_EOL;
	echo $e->getTraceAsString(), PHP_EOL;
}
