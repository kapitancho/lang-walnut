<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;

interface NamedTypeHydrator {
	public function tryHydrateByName(
		NamedType $targetType,
		HydrationRequest $request
	): null|HydrationSuccess|HydrationFailure;
}