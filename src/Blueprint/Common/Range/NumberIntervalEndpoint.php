<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;

interface NumberIntervalEndpoint {
	public Number $value { get; }
	public bool $inclusive { get; }
}