<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use RuntimeException;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;

final class InvalidNumberInterval extends RuntimeException {
	public function __construct(
		public readonly MinusInfinity|NumberIntervalEndpointInterface $start,
		public readonly PlusInfinity|NumberIntervalEndpointInterface $end
	) {
		parent::__construct(
			message: sprintf("%s is not a valid number interval",
				($start instanceof NumberIntervalEndpointInterface && $start->inclusive ? '[' : '(') .
				($start instanceof NumberIntervalEndpointInterface ? $start->value : '') .
				'..' .
				($end instanceof NumberIntervalEndpointInterface ? $end->value : '') .
				($end instanceof NumberIntervalEndpointInterface && $end->inclusive ? ']' : ')')
			)
		);
	}
}