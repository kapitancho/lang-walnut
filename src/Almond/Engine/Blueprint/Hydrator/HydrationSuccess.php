<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface HydrationSuccess extends HydrationResult {
	/** @var array{} $errors */
	public array $errors { get; }

	public Value $hydratedValue { get; }
}