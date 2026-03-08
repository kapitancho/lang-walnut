<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

abstract readonly class SequentialExpressionBase implements Expression, JsonSerializable {

	/** @param array<Expression> $expressions */
	public function __construct(
		protected TypeRegistry $typeRegistry,
		protected ValueRegistry $valueRegistry,

		public array $expressions
	) {}

	/** @param array<Type> $expressionTypes */
	abstract protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic): Type;

	public function validateInContext(
		ValidationContext $validationContext,
	): ValidationContext|ValidationFailure {
		/** @var ValidationFailure|null $failure */
		$failure = null;

		$set = [];
		$dynamic = 0;

		$expressionTypes = [];
		$returnTypes = [];
		$earlyReturnResult = null;
		foreach($this->expressions as $key => $expression) {
			$step = $expression->validateInContext($validationContext);
			if ($step instanceof ValidationFailure) {
				if (!$expression->isScopeSafe()) {
					return $step;
				}
				$failure = $failure === null ? $step : $failure->mergeWith($step);
			} elseif (!$earlyReturnResult) {
				if ($expression instanceof ConstantExpression) {
					$set[(string)$expression] = true;
				} else {
					$dynamic++;
				}
				$returnTypes[$key] = $step->returnType;
				if ($step->expressionType instanceof NothingType) {
					$earlyReturnResult = $validationContext->withExpressionType($step->expressionType)
						->withReturnType(
							$this->typeRegistry->union(array_values($returnTypes))
						);
				}
				$expressionTypes[$key] = $step->expressionType;
				$validationContext = $step;
			}
		}
		return $failure ?? $earlyReturnResult ?? $validationContext
			->withExpressionType(
				$this->buildExpressionType(
					$expressionTypes, count($set), $dynamic
				)
			)
			->withReturnType($this->typeRegistry->union(array_values($returnTypes)));
	}

	public function isScopeSafe(): bool {
		return array_all(
			$this->expressions,
			fn(Expression $expression) => $expression->isScopeSafe()
		);
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		foreach($this->expressions as $expression) {
			$dependencyContext = $expression->validateDependencies($dependencyContext);
		}
		return $dependencyContext;
	}

	/** @param array<Value> $expressionValues */
	abstract protected function buildExpressionValue(array $expressionValues): Value;

	public function execute(
		ExecutionContext $executionContext,
	): ExecutionContext {
		$values = [];
		foreach($this->expressions as $key => $expression) {
			$executionContext = $expression->execute($executionContext);
			$values[$key] = $executionContext->value;
		}
		return $executionContext->withValue(
			$this->buildExpressionValue($values)
		);
	}

}