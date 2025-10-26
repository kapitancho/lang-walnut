<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPoint as HttpEntryPointInterface;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\HttpEntryPointBuilder as HttpEntryPointBuilderInterface;

final readonly class HttpEntryPoint implements HttpEntryPointInterface {
	public function __construct(
		private HttpEntryPointBuilderInterface $httpEntryPointBuilder
	) {}

	public function call(string $source, HttpRequest $httpRequest): HttpResponse {
		return $this->httpEntryPointBuilder
			->build($source)
			->call($httpRequest);
	}
}