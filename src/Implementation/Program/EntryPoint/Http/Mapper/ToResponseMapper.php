<?php

namespace Walnut\Lang\Implementation\Program\EntryPoint\Http\Mapper;

use Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Program\EntryPoint\Http\Message\HttpResponse;

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