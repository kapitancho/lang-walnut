<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Http\Message;

interface HttpRequest {
	public HttpProtocolVersion $protocolVersion { get; }
	public HttpRequestMethod $method { get; }
	public string $target { get; }
	/** @var array<string, list<string>> */
	public array $headers { get; }
	public string|null $body { get; }
}