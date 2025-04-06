<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequestMethod;
use Walnut\Lang\Implementation\Compilation\Compiler;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\HttpEntryPoint;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\HttpEntryPointBuilder;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Test\BaseProgramTestHelper;

class HttpEntryPointTest extends BaseProgramTestHelper {

	public function testCall(): void {
		$this->programContext->globalScopeBuilder->addVariable(
			new VariableNameIdentifier('main'),
			$this->valueRegistry->function(
				$this->typeRegistry->string(),
				new VariableNameIdentifier('args'),
				$this->typeRegistry->nothing,
				$this->typeRegistry->integer(),
				$this->expressionRegistry->functionBody(
					$this->expressionRegistry->constant(
						$this->valueRegistry->integer(42)
					)
				)
			)
		);
		$moduleLookupContext = $this->createMock(ModuleLookupContext::class);
		$moduleLookupContext->method('sourceOf')
			->willReturnCallback(fn(string $module) => match($module) {
				'core/core' => file_get_contents(__DIR__ . '/../../../../../../core-nut-lib/core.nut'),
				'http/message' => file_get_contents(__DIR__ . '/../../../../../../core-nut-lib/http/message.nut'),
				'test' => 'module test %% http/message: handleHttpRequest = ^request: HttpRequest => HttpResponse :: [
				    protocolVersion: request.protocolVersion,
				    headers: request.headers,
				    body: request.body,
				    statusCode: 200
				];',
				default => ''
			});

		$compiler = new Compiler($moduleLookupContext);
		$httpEntryPoint = new HttpEntryPoint(new HttpEntryPointBuilder($compiler));
		$headers = ['Content-Type' => ['text/plain']];
		$response = $httpEntryPoint->call('test', new HttpRequest(
			HttpProtocolVersion::http_1_1,
			HttpRequestMethod::post,
			'target',
			$headers,
			'body'
		));
		$this->assertEquals(HttpProtocolVersion::http_1_1, $response->protocolVersion);
		$this->assertEquals($headers, $response->headers);
		$this->assertEquals('body', $response->body);
		$this->assertEquals(200, $response->statusCode);

		$response = $httpEntryPoint->call('test', new HttpRequest(
			HttpProtocolVersion::fromName('unknown'),
			HttpRequestMethod::get,
			'target',
			$headers,
			null
		));
		$this->assertEquals(HttpProtocolVersion::http_1_1, $response->protocolVersion);
		$this->assertEquals($headers, $response->headers);
		$this->assertEquals(null, $response->body);
		$this->assertEquals(200, $response->statusCode);
	}

}