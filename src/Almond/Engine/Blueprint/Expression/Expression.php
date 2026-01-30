<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Expression;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;

interface Expression extends Stringable {
	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure;
	public function isScopeSafe(): bool;
	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
	/** @throws ExecutionException|ExecutionEarlyReturn */
	public function execute(ExecutionContext $executionContext): ExecutionContext;
}