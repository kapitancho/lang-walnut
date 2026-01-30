<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface HydrationFactory {
	public function forValue(Value $value): HydrationRequest;
}