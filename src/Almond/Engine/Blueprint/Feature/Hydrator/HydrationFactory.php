<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface HydrationFactory {
	public function forValue(Value $value): HydrationRequest;
}