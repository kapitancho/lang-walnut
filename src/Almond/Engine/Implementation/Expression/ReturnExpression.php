<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;

final readonly class ReturnExpression implements Expression {

	public function __construct(
		private TypeRegistry $typeRegistry,

		private Expression $returnedExpression
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->returnedExpression->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		return $result->withExpressionType(
			$this->typeRegistry->nothing
		)->withReturnType($this->typeRegistry->union([
			$result->returnType,
			$result->expressionType
		]));
	}

	// TODO: consider if this is true: Return expressions are always scope safe because they terminate execution flow.
	//public function isScopeSafe(): bool { return true; }

	public function isScopeSafe(): bool { return $this->returnedExpression->isScopeSafe(); }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->returnedExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		throw new ExecutionEarlyReturn(
			$this->returnedExpression->execute($executionContext)->value
		);
	}

	public function __toString(): string {
		return sprintf(
			"=> %s",
			$this->returnedExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'return',
			'returnedExpression' => $this->returnedExpression
		];
	}
}