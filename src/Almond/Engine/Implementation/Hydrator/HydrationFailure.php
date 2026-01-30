<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationError;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure as HydrationFailureInterface;

final readonly class HydrationFailure implements HydrationFailureInterface {

	/** @param list<HydrationError> $errors */
	public function __construct(public array $errors) {}

	public function mergeFailure(HydrationFailureInterface $failure): HydrationFailureInterface {
		return clone($this, [
			'errors' => array_merge($this->errors, $failure->errors)
		]);
	}
}