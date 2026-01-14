<?php

namespace Walnut\Lang\Blueprint\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Type\ComplexType;
use Walnut\Lang\Blueprint\Value\Value;

interface ComplexTypeHydrator {
	/** @throws HydrationException */
	public function hydrate(Hydrator $hydrator, Value $value, ComplexType $targetType, string $hydrationPath): Value;
}