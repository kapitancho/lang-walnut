<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ConstantExpression as ConstantExpressionInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValueScope;

final readonly class ConstantExpression implements ConstantExpressionInterface, JsonSerializable {

	public function __construct(
		private ValidationFactory $validationFactory,
		private ValueRegistry $valueRegistry,
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
		return $executionContext->withValue(
			$this->withAppliedVariableValueScope($this->value, $executionContext->variableValueScope)
		);
	}

	private function withAppliedVariableValueScope(Value $value, VariableValueScope $variableValueScope): Value {
		$l = fn(TupleValue|RecordValue|SetValue $v) => array_map(
			fn($item) => $this->withAppliedVariableValueScope($item, $variableValueScope),
			$v->values
		);

		return match(true) {
			$value instanceof FunctionValue => $value->withVariableValueScope($variableValueScope),
			$value instanceof TupleValue => $this->valueRegistry->tuple($l($value)),
			$value instanceof RecordValue =>
				/** @phpstan-ignore argument.type */
				$this->valueRegistry->record($l($value)),
			$value instanceof SetValue => $this->valueRegistry->set($l($value)),
			$value instanceof DataValue => $this->valueRegistry->data(
				$value->type->name,
				$this->withAppliedVariableValueScope($value->value, $variableValueScope)
			),
			$value instanceof ErrorValue => $this->valueRegistry->error(
				$this->withAppliedVariableValueScope($value->errorValue, $variableValueScope)
			),
			$value instanceof MutableValue => $this->valueRegistry->mutable(
				$value->targetType,
				$this->withAppliedVariableValueScope($value->value, $variableValueScope)
			),
			default => $value
		};
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