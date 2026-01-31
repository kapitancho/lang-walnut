<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody as FunctionBodyInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

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