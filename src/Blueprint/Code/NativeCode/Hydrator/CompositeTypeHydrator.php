<?php

namespace Walnut\Lang\Blueprint\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Type\CompositeType;
use Walnut\Lang\Blueprint\Value\Value;

interface CompositeTypeHydrator {
	/** @throws HydrationException */
	public function hydrate(Hydrator $hydrator, Value $value, CompositeType $targetType, string $hydrationPath): Value;
}