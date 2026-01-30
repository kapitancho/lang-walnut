<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;

final readonly class VariableNameExpression implements Expression, JsonSerializable {
	public function __construct(
		public VariableName $variableName
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$variableType = $validationContext->variableScope->typeOf($this->variableName);
		return $variableType ?
			$validationContext->withExpressionType($variableType) :
			$validationContext->withError(
				ValidationErrorType::undefinedVariable,
				sprintf("Undefined variable '%s'.", $this->variableName),
				$this
			);
	}

	public function isScopeSafe(): bool { return true; }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$value = $executionContext->variableValueScope->valueOf($this->variableName);
		return $value ? $executionContext->withValue($value) : throw new ExecutionException(
			sprintf("Undefined variable '%s'.", $this->variableName)
		);
	}

	public function __toString(): string {
		return (string)$this->variableName;
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'variableName',
			'variableName' => $this->variableName
		];
	}
}