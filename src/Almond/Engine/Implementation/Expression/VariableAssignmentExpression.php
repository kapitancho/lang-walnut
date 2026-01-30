<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\ConstantExpression as ConstantExpressionInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\FunctionValue;

final readonly class VariableAssignmentExpression implements Expression {

	public function __construct(
		public VariableName $variableName,
		public Expression $assignedExpression
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		if ($this->assignedExpression instanceof ConstantExpressionInterface &&
			($v = $this->assignedExpression->value) instanceof FunctionValue
		) {
			$validationContext = $validationContext->withAddedVariableType(
				$this->variableName,
				$v->type
			);
		}
		$validationContext = $this->assignedExpression->validateInContext($validationContext);
		if ($validationContext instanceof ValidationContext) {
			$validationContext = $validationContext->withAddedVariableType(
				$this->variableName,
				$validationContext->expressionType
			);
		}
		return $validationContext;
	}

	public function isScopeSafe(): bool { return false; }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->assignedExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$executionContext = $this->assignedExpression->execute($executionContext);
		$value = $executionContext->value;
		if ($value instanceof FunctionValue && $this->assignedExpression instanceof ConstantExpressionInterface) {
			$value = $value->withSelfReferenceAs($this->variableName);
		}
		$executionContext = $executionContext->withAddedVariableValue(
			$this->variableName,
			$value
		);
		return $executionContext;
	}

	public function __toString(): string {
		return sprintf(
			"%s = %s",
			$this->variableName,
			$this->assignedExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'variableAssignment',
			'variableName' => $this->variableName,
			'assignedExpression' => $this->assignedExpression
		];
	}
}