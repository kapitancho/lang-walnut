<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http\Message;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest as HttpRequestInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequestMethod;

final readonly class HttpRequest implements HttpRequestInterface {
	/** @param array<string, list<string>> $headers */
	public function __construct(
		public HttpProtocolVersion $protocolVersion,
		public HttpRequestMethod $method,
		public string $target,
		public array $headers,
		public string|null $body
	) {}
}