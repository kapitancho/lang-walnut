<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Http\Message;

interface HttpResponse {
	public HttpProtocolVersion $protocolVersion { get; }
	public int $statusCode { get; }
	/** @return array<string, list<string>> */
	public array $headers { get; }
	public string|null $body { get; }
}