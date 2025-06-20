<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use RuntimeException;

final class InvalidNumberRange extends RuntimeException {
	public function __construct() {
		parent::__construct(
			"Invalid number range - it must contain at least one interval"
		);
	}
}