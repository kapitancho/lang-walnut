<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator;

interface HydrationResult {
	/** @var list<HydrationError> $errors */
	public array $errors { get; }

	public function mergeFailure(HydrationFailure $failure): HydrationFailure;
}