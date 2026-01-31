<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

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
			'expressionType' => 'VariableName',
			'variableName' => $this->variableName
		];
	}
}