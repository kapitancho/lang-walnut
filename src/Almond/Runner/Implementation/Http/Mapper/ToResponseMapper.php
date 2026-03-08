<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Http\Mapper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Runner\Blueprint\Http\Message\HttpProtocolVersion;
use Walnut\Lang\Almond\Runner\Implementation\Http\Message\HttpResponse;

final readonly class ToResponseMapper {
	public function mapToResponse(RecordValue $response): HttpResponse {
		/** @var EnumerationValue $protocolVersion */
		$protocolVersion = $response->valueOf('protocolVersion');

		/** @var IntegerValue $statusCode */
		$statusCode = $response->valueOf('statusCode');

		/** @var RecordValue $headers */
		$headers = $response->valueOf('headers');

		/** @var StringValue|NullValue $body */
		$body = $response->valueOf('body');

		return new HttpResponse(
			HttpProtocolVersion::fromName(
				$protocolVersion->name->identifier
			),
			(int)$statusCode->literalValue->value,
			array_map(
				fn(TupleValue $headerValues): array => array_map(
					/** @phpstan-ignore argument.type */
					fn(StringValue $headerValue): string => $headerValue->literalValue,
					$headerValues->values
				),
				$headers->values
			),
			$body instanceof StringValue ? $body->literalValue : null,
		);
	}
}