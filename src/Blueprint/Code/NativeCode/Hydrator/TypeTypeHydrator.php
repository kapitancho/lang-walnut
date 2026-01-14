<?php

namespace Walnut\Lang\Blueprint\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;

interface TypeTypeHydrator {
	/** @throws HydrationException */
	public function hydrate(Value $value, TypeType $targetType, string $hydrationPath): TypeValue;
}