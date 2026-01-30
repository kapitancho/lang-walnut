<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

interface Type extends Stringable {
	public function isSubtypeOf(Type $ofType): bool;

	public function validate(ValidationRequest $request): ValidationResult;

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure;
}