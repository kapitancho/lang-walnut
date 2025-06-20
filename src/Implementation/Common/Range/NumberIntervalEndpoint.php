<?php

namespace Walnut\Lang\Implementation\Common\Range;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;

final readonly class NumberIntervalEndpoint implements NumberIntervalEndpointInterface {
	public function __construct(
		public Number $value,
		public bool $inclusive
	) {}
}