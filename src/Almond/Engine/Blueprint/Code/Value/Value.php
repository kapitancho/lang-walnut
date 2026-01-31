<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

interface Value extends Stringable {

	public Type $type { get; }
	public function equals(Value $other): bool;

	public function validate(ValidationRequest $request): ValidationResult;

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
}