<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Type\NamedType;

interface NamedTypeHydrator {
	public function tryHydrateByName(
		NamedType $targetType,
		HydrationRequest $request
	): null|HydrationSuccess|HydrationFailure;
}