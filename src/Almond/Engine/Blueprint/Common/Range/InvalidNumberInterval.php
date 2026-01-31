<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Range;

use RuntimeException;

final class InvalidNumberInterval extends RuntimeException {
	public function __construct(
		public readonly MinusInfinity|NumberIntervalEndpoint $start,
		public readonly PlusInfinity|NumberIntervalEndpoint $end
	) {
		parent::__construct(
			message: sprintf("%s is not a valid number interval",
				($start instanceof NumberIntervalEndpoint && $start->inclusive ? '[' : '(') .
				($start instanceof NumberIntervalEndpoint ? $start->value : '') .
				'..' .
				($end instanceof NumberIntervalEndpoint ? $end->value : '') .
				($end instanceof NumberIntervalEndpoint && $end->inclusive ? ']' : ')')
			)
		);
	}
}
