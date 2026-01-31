<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

interface Type extends Stringable {
	public function isSubtypeOf(Type $ofType): bool;

	public function validate(ValidationRequest $request): ValidationResult;

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure;
}