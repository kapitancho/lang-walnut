<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface HydrationSuccess extends HydrationResult {
	/** @var array{} $errors */
	public array $errors { get; }

	public Value $hydratedValue { get; }
}