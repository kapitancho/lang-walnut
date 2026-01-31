<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Function;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

interface FunctionBody extends Stringable {
	public Expression $expression { get; }
	public function validateInContext(ValidationContext $validationContext): ValidationSuccess|ValidationFailure;
	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext;
	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): Value;
}