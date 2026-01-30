<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\ConstantExpression;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

//TODO: not a trait but a separate class
abstract readonly class SequentialExpressionBase implements Expression{

	/** @param array<Expression> $expressions */
	public function __construct(
		protected TypeRegistry $typeRegistry,
		protected ValueRegistry $valueRegistry,

		public array $expressions
	) {}

	/** @param array<Type> $expressionTypes */
	abstract protected function buildExpressionType(array $expressionTypes, int $set, int $dynamic,);

	public function validateInContext(
		ValidationContext $validationContext,
	): ValidationContext|ValidationFailure {
		$failure = null;

		$set = [];
		$dynamic = 0;

		$expressionTypes = [];
		$returnTypes = [];
		foreach($this->expressions as $key => $expression) {
			if ($expression instanceof ConstantExpression) {
				$set[(string)$expression] = true;
			} else {
				$dynamic++;
			}

			$step = $expression->validateInContext($validationContext);
			if ($step instanceof ValidationFailure) {
				if (!$expression->isScopeSafe()) {
					return $step;
				}
				$failure = $failure === null ? $step : $failure->mergeWith($step);
			} else {
				$expressionTypes[$key] = $step->expressionType;
				$returnTypes[$key] = $step->returnType;
				$validationContext = $step;
			}
		}
		return $failure ?? $validationContext
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