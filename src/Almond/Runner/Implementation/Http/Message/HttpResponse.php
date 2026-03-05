<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Http\Message;

use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpResponse as HttpResponseInterface;

final readonly class HttpResponse implements HttpResponseInterface {
	/** @param array<string, list<string>> $headers */
	public function __construct(
		public HttpProtocolVersion $protocolVersion,
		public int $statusCode,
		public array $headers,
		public string|null $body
	) {}
}