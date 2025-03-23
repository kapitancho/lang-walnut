<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message;
interface HttpRequest {
	public HttpProtocolVersion $protocolVersion { get; }
	public HttpRequestMethod $method { get; }
	public string $target { get; }
	/** @return array<string, list<string>> */
	public array $headers { get; }
	public string|null $body { get; }
}