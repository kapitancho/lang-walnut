<?php

namespace Walnut\Lang\Blueprint\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Type\SimpleType;
use Walnut\Lang\Blueprint\Value\Value;

interface SimpleTypeHydrator {
	/** @throws HydrationException */
	public function hydrate(Value $value, SimpleType $targetType, string $hydrationPath): Value;
}