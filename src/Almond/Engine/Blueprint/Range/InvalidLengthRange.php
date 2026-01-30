<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Range;

use BcMath\Number;
use RuntimeException;

final class InvalidLengthRange extends RuntimeException {
	public function __construct(
		public readonly Number $minLength,
		public readonly Number|PlusInfinity $maxLength
	) {
		parent::__construct(
			message: sprintf("%s..%s is not a valid length range", $this->minLength, $this->maxLength)
		);
	}
}