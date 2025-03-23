<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse;

interface SourceHttpEntryPoint {
	public function call(HttpRequest $httpRequest): HttpResponse;
}