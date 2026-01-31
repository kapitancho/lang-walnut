<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ConstantExpression as ConstantExpressionInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class ConstantExpression implements ConstantExpressionInterface, JsonSerializable {

	public function __construct(
		private ValidationFactory $validationFactory,
		public Value $value
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$validationResult = $this->value->validate(
			$this->validationFactory->fromVariableScope($validationContext->variableScope)
		);
		return $validationResult instanceof ValidationFailure ? $validationResult :
			$validationContext->withExpressionType($this->value->type);
	}

	public function isScopeSafe(): bool { return true; }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->value->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$variableValueScope = $executionContext->variableValueScope;
		$value = $this->value;
		if ($value instanceof FunctionValue) {
			// TODO: handle function values inside a constant expression properly
			$value = $value->withVariableValueScope($variableValueScope);
		}
		return $executionContext->withValue($value);
	}

	public function __toString(): string {
		return (string)$this->value;
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'constant',
			'value' => $this->value
		];
	}
}