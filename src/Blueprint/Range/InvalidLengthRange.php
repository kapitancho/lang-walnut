<?php

namespace Walnut\Lang\Blueprint\Range;

use BcMath\Number;
use RuntimeException;

final class InvalidLengthRange extends RuntimeException {
	public function __construct(
		public readonly Number $minLength,
		public readonly Number|PlusInfinity $maxLength
	) {
		parent::__construct();
	}
}