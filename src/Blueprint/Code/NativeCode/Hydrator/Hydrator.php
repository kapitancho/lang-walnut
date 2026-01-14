<?php

namespace Walnut\Lang\Blueprint\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface Hydrator {
	/** @throws HydrationException */
	public function hydrate(Value $value, Type $targetType, string $hydrationPath): Value;
}