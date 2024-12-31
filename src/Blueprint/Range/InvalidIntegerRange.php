<?php

namespace Walnut\Lang\Blueprint\Range;

use BcMath\Number;
use RuntimeException;

final class InvalidIntegerRange extends RuntimeException {
	public function __construct(
		public readonly Number|MinusInfinity $minValue,
		public readonly Number|PlusInfinity $maxValue
	) {
		parent::__construct();
	}
}