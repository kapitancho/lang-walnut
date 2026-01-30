<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody as FunctionBodyInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class FunctionBody implements FunctionBodyInterface {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValidationFactory $validationFactory,

		public Expression $expression
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationSuccess|ValidationFailure {
		$validationContext = $this->expression->validateInContext($validationContext);
		return $validationContext instanceof ValidationContext ?
			$this->validationFactory->validationSuccess(
				$this->typeRegistry->union([
					$validationContext->expressionType,
					$validationContext->returnType
				])
			) : $validationContext;
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->expression->validateDependencies($dependencyContext);
	}

	/** @throws ExecutionException */
	public function execute(ExecutionContext $executionContext): Value {
		try {
			return $this->expression->execute($executionContext)->value;
		} catch (ExecutionEarlyReturn $return) {
			return $return->returnValue;
		}
	}

	public function __toString(): string {
		return (string)$this->expression;
	}

}