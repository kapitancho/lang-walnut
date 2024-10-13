<?php

namespace Walnut\Lang\Blueprint\Range;

use RuntimeException;

final class InvalidRealRange extends RuntimeException {
	public function __construct(
		public readonly float|MinusInfinity $minValue,
		public readonly float|PlusInfinity $maxValue
	) {
		parent::__construct();
	}
}