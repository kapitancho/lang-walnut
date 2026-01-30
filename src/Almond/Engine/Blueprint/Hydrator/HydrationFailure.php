<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Hydrator;

interface HydrationFailure extends HydrationResult {
	/** @var non-empty-list<HydrationError> $errors */
	public array $errors { get; }
}