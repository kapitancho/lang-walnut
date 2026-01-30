<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure as HydrationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess as HydrationSuccessInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class HydrationSuccess implements HydrationSuccessInterface {

	/** @var array{} $errors */
	public array $errors;

	public function __construct(
		public Value $hydratedValue,
	) {
		$this->errors = [];
	}

	public function mergeFailure(HydrationFailureInterface $failure): HydrationFailureInterface {
		return $failure;
	}
}