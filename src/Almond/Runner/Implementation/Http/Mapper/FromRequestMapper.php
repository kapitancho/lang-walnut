<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Http\Mapper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpRequest;

final readonly class FromRequestMapper {
	public function mapFromRequest(HttpRequest $request, ValueRegistry $valueRegistry): Value {
		return $valueRegistry->record([
			'protocolVersion' => $valueRegistry->enumeration(
				new TypeName('HttpProtocolVersion'),
				new EnumerationValueName($request->protocolVersion->name)
			),
			'method' => $valueRegistry->enumeration(
				new TypeName('HttpRequestMethod'),
				new EnumerationValueName($request->method->name)
			),
			'target' => $valueRegistry->string($request->target),
			'headers' => $valueRegistry->record(array_map(
				fn(array $headerValues): Value => $valueRegistry->tuple(
					array_map(
						fn(string $headerValue): Value => $valueRegistry->string($headerValue),
						$headerValues
					),
				),
				$request->headers
			)),
			'body' => $request->body === null ? $valueRegistry->null : $valueRegistry->string($request->body)
		]);
	}
}