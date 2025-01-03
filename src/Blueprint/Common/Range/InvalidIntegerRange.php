<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;
use RuntimeException;

final class InvalidIntegerRange extends RuntimeException {
	public function __construct(
		public readonly Number|MinusInfinity $minValue,
		public readonly Number|PlusInfinity $maxValue
	) {
		parent::__construct(
			message: sprintf("%s..%s is not a valid Integer range", $minValue, $maxValue)
		);
	}
}