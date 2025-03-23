<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http\Message;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse as HttpResponseInterface;

final readonly class HttpResponse implements HttpResponseInterface {
	/** @param array<string, list<string>> $headers */
	public function __construct(
		public HttpProtocolVersion $protocolVersion,
		public int $statusCode,
		public array $headers,
		public string|null $body
	) {}
}