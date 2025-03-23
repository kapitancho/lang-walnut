<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http\Mapper;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\EntryPoint\EntryPointProvider;
use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpRequest;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class FromRequestMapper {
	public function __construct(
		private EntryPointProvider $entryPointProvider
	) {}

	public function mapFromRequest(HttpRequest $request): Value {
		$tr = $this->entryPointProvider->typeRegistry;
		$vr = $this->entryPointProvider->valueRegistry;
		return $vr->record([
			'protocolVersion' => $vr->enumerationValue(
				new TypeNameIdentifier('HttpProtocolVersion'),
				new EnumValueIdentifier($request->protocolVersion->name)
			),
			'method' => $vr->enumerationValue(
				new TypeNameIdentifier('HttpRequestMethod'),
				new EnumValueIdentifier($request->method->name)
			),
			'target' => $vr->string($request->target),
			'headers' => $vr->record(array_map(
				fn(array $headerValues): Value => $vr->tuple(
					array_map(
						fn(string $headerValue): Value => $vr->string($headerValue),
						$headerValues
					),
				),
				$request->headers
			)),
			'body' => $request->body === null ? $vr->null : $vr->string($request->body)
		]);
	}
}