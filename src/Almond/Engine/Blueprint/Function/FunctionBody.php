<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Function;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface FunctionBody extends Stringable {
	public Expression $expression { get; }
	public function validateInContext(ValidationContext $validationContext): ValidationSuccess|ValidationFailure;
	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): Value;
}