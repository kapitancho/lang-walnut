<?php


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequestMethod;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Message\HttpRequest;

final readonly class PsrHttpAdapter {
	public function __construct(
		private ResponseFactoryInterface $responseFactory,
		private StreamFactoryInterface $streamFactory,
	) {}

	public function buildRequest(RequestInterface $request): HttpRequest {
		// Parse protocol version
		$protocol = $request->getProtocolVersion();
		$protocolVersion = match($protocol) {
			'1.0' => HttpProtocolVersion::http_1_0,
			'1.1' => HttpProtocolVersion::http_1_1,
			'2.0', '2' => HttpProtocolVersion::http_2,
			default => HttpProtocolVersion::http_1_1,
		};

		// Parse request method
		$methodString = $request->getMethod();
		$method = HttpRequestMethod::from($methodString);

		// Get request target (URI)
		$target = $request->getRequestTarget();

		// Create HTTP request
		return new HttpRequest(
			$protocolVersion,
			$method,
			$target,
			$request->getHeaders(),
			$request->getBody()?->getContents() ?? null
		);
	}

	public function buildResponse(HttpResponse $response): ResponseInterface {
		$result = $this->responseFactory->createResponse(
			$response->statusCode
		)->withProtocolVersion(
			match($response->protocolVersion) {
				HttpProtocolVersion::http_1_0 => '1.0',
				HttpProtocolVersion::http_2 => '2.0',
				HttpProtocolVersion::http_3 => '3.0',
				/*HttpProtocolVersion::http_1_1,*/ default => '1.1'
			}
		);
		if ($response->body !== null) {
			$result = $result->withBody($this->streamFactory->createStream($response->body));
		}

		foreach ($response->headers as $name => $values) {
			foreach ($values as $value) {
				$result = $result->withAddedHeader($name, $value);
			}
		}

		return $result;
	}

}