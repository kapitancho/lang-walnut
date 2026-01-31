<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Range;

use BcMath\Number;

final readonly class NumberIntervalEndpoint {
	public function __construct(
		public Number $value,
		public bool $inclusive
	) {}
}
