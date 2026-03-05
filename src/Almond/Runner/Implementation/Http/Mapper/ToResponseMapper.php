<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Http\Mapper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Almond\Runner\Implementation\Http\Message\HttpResponse;

final readonly class ToResponseMapper {
	public function mapToResponse(Value $response): HttpResponse {
		return new HttpResponse(
			HttpProtocolVersion::fromName(
				$response->valueOf('protocolVersion')->name->identifier
			),
			(int)$response->valueOf('statusCode')->literalValue->value,
			array_map(
				fn(Value $headerValues): array => array_map(
					fn(Value $headerValue): string => $headerValue->literalValue,
					$headerValues->values
				),
				$response->valueOf('headers')->values
			),
			$response->valueOf('body')->literalValue,
		);
	}
}