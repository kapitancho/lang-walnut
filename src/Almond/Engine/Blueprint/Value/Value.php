<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

interface Value extends Stringable {

	public Type $type { get; }
	public function equals(Value $other): bool;

	public function validate(ValidationRequest $request): ValidationResult;

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
}