<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message;
interface HttpResponse {
	public HttpProtocolVersion $protocolVersion { get; }
	public int $statusCode { get; }
	public array $headers { get; }
	public string|null $body { get; }

}