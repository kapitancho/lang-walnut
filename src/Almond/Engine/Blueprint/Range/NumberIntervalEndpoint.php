<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Range;

use BcMath\Number;

interface NumberIntervalEndpoint {
	public Number $value { get; }
	public bool $inclusive { get; }
}
