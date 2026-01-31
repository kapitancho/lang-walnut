<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\Hydrator;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure as HydrationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess as HydrationSuccessInterface;

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