<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Http\Message;

use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpRequest as HttpRequestInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpRequestMethod;

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