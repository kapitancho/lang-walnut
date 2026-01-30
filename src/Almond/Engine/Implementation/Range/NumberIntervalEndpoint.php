<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Range;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;

final readonly class NumberIntervalEndpoint implements NumberIntervalEndpointInterface {
	public function __construct(
		public Number $value,
		public bool $inclusive
	) {}
}
