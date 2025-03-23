<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse;

interface HttpEntryPoint {
	public function call(string $source, HttpRequest $httpRequest): HttpResponse;
}