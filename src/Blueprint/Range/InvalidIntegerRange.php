<?php

namespace Walnut\Lang\Blueprint\Range;

use RuntimeException;

final class InvalidIntegerRange extends RuntimeException {
	public function __construct(
		public readonly int|MinusInfinity $minValue,
		public readonly int|PlusInfinity $maxValue
	) {
		parent::__construct();
	}
}