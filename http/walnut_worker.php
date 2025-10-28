<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Http\PSR7Worker;
use Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\HttpEntryPointFactory;

include "vendor/autoload.php";

// Load configuration
$cfg = @json_decode(@file_get_contents('nutcfg.json') ?? '{}', true);
$sourceRoot = $cfg['sourceRoot'] ?? 'walnut-src';
$packages = $cfg['packages'] ?? ['core' => __DIR__ . '/../core-nut-lib'];
$httpModule = $cfg['httpModule'] ?? 'server';

$factory = new HttpEntryPointFactory(new PackageConfiguration($sourceRoot, $packages));
$ep = $factory->entryPointBuilder->build($httpModule);

$worker = Worker::create();
$psrFactory = new Psr17Factory();

$worker = new PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);
require_once __DIR__ . '/PsrHttpAdapter.php';
$walnutPsrAdapter = new \PsrHttpAdapter($psrFactory, $psrFactory);

while ($req = $worker->waitRequest()) {
	try {
		$walnutRequest = $walnutPsrAdapter->buildRequest($req);
		$walnutResponse = $ep->call($walnutRequest);
		$rsp = $walnutPsrAdapter->buildResponse($walnutResponse);

		$worker->respond($rsp);
	} catch (Throwable $e) {
		$worker->getWorker()->error((string)$e);
	}
}