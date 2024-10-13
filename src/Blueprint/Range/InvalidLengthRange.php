<?php

namespace Walnut\Lang\Blueprint\Range;

use RuntimeException;

final class InvalidLengthRange extends RuntimeException {
	public function __construct(
		public readonly int $minLength,
		public readonly int|PlusInfinity $maxLength
	) {
		parent::__construct();
	}
}