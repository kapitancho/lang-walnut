<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\EntryPoint\EntryPointProvider;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpResponse;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\SourceHttpEntryPoint as SourceHttpEntryPointInterface;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Mapper\FromRequestMapper;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Mapper\ToResponseMapper;

final readonly class SourceHttpEntryPoint implements SourceHttpEntryPointInterface {
	public function __construct(
		private EntryPointProvider $entryPointProvider
	) {}

	public function call(HttpRequest $httpRequest): HttpResponse {
		$requestValue = new FromRequestMapper($this->entryPointProvider)
			->mapFromRequest($httpRequest);
		$ep = $this->entryPointProvider->program->getEntryPoint(
			new TypeNameIdentifier('HttpRequestHandler')
		);
		/*$ep = $this->entryPointProvider->program->getEntryPoint(
			new VariableNameIdentifier('handleHttpRequest'),
			$tr->withName(new TypeNameIdentifier('HttpRequest')),
			$tr->withName(new TypeNameIdentifier('HttpResponse')),
		);*/
		$returnValue = $ep->call($requestValue);
		return new ToResponseMapper()->mapToResponse($returnValue);
	}
}