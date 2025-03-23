<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPoint as HttpEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse;

final readonly class HttpEntryPoint implements HttpEntryPointInterface {
	public function __construct(
		private HttpEntryPointBuilder $httpEntryPointBuilder
	) {}

	public function call(string $source, HttpRequest $httpRequest): HttpResponse {
		return $this->httpEntryPointBuilder
			->build($source)
			->call($httpRequest);
	}
}