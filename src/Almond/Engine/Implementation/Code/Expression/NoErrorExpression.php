<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class NoErrorExpression implements Expression, JsonSerializable {

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
			'expressionType' => 'NoError',
			'targetExpression' => $this->targetExpression
		];
	}
}