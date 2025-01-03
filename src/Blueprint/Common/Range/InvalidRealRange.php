<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;
use RuntimeException;

final class InvalidRealRange extends RuntimeException {
	public function __construct(
		public readonly Number|MinusInfinity $minValue,
		public readonly Number|PlusInfinity $maxValue
	) {
		parent::__construct(
			message: sprintf("%s..%s is not a valid Real range", $minValue, $maxValue)
		);
	}
}