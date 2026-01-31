<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

interface Expression extends Stringable {
	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure;
	public function isScopeSafe(): bool;
	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
	/** @throws ExecutionException|ExecutionEarlyReturn */
	public function execute(ExecutionContext $executionContext): ExecutionContext;
}