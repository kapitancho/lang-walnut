<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue;

final readonly class NoErrorExpression implements Expression {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private Expression $targetExpression) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->targetExpression->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$expressionType = $result->expressionType;
		if ($expressionType instanceof ResultType) {
			return $result->withExpressionType(
				$expressionType->returnType
			)->withReturnType(
				$this->typeRegistry->result(
					$result->returnType,
					$expressionType->errorType
				)
			);
		}
		return $result;
	}

	public function isScopeSafe(): bool { return $this->targetExpression->isScopeSafe(); }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->targetExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$result = $this->targetExpression->execute($executionContext);
		if ($result->value instanceof ErrorValue) {
			throw new ExecutionEarlyReturn($result->value);
		}
		return $result;
	}

	public function __toString(): string {
		return sprintf(
			"(%s)?",
			$this->targetExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'noExternalError',
			'targetExpression' => $this->targetExpression
		];
	}
}