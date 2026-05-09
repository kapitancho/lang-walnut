<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class MatchEmptyExpression implements Expression, JsonSerializable {
	use BaseType;

	public function __construct(
		private TypeRegistry $typeRegistry,

		public Expression $target,
		public Expression $onEmpty,
		public Expression|null $else,
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->target->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$elseExpressionType = $this->toBaseType($result->expressionType);

		$returnTypes = [$result->returnType];

		$isOptional = false;
		if ($elseExpressionType instanceof OptionalType) {
			$isOptional = true;
			$elseExpressionType = $elseExpressionType->valueType;
		}

		$innerContext = $result;
		$isVar = $this->target instanceof VariableNameExpression || $this->target instanceof VariableAssignmentExpression;
		if ($isVar) {
			$innerContext = $innerContext->withAddedVariableType(
				$this->target->variableName,
				$this->typeRegistry->empty,
			);
		}
		$retValue = $this->onEmpty->validateInContext($innerContext);
		if ($retValue instanceof ValidationFailure) {
			return $retValue;
		}
		if ($isOptional) {
			$onEmptyExpressionType = $retValue->expressionType;
			$returnTypes[] = $retValue->returnType;
		} else {
			$onEmptyExpressionType = $this->typeRegistry->nothing;
		}

		if ($this->else) {
			$innerContext = $result;
			if ($isVar) {
				$innerContext = $innerContext->withAddedVariableType(
					$this->target->variableName,
					$elseExpressionType,
				);
			}
			$retValue = $this->else->validateInContext($innerContext);
			if ($retValue instanceof ValidationFailure) {
				return $retValue;
			}
			if (!$elseExpressionType instanceof NothingType) {
				$elseExpressionType = $retValue->expressionType;
				$returnTypes[] = $retValue->returnType;
			}
		}
		return $result
			->withExpressionType(
				$this->typeRegistry->union(
					[$onEmptyExpressionType, $elseExpressionType]
				)
			)
			->withReturnType(
				$this->typeRegistry->union($returnTypes)
			);
	}

	public function isScopeSafe(): bool {
		return $this->target->isScopeSafe();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		$dependencyContext = $dependencyContext
			|> $this->target->validateDependencies(...)
			|> $this->onEmpty->validateDependencies(...);
		if ($this->else !== null) {
			$dependencyContext = $this->else->validateDependencies($dependencyContext);
		}
		return $dependencyContext;
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$executionContext = $this->target->execute($executionContext);
		$value = $executionContext->value;
		$isVar = $this->target instanceof VariableNameExpression || $this->target instanceof VariableAssignmentExpression;
		if ($value instanceof EmptyValue) {
			$innerContext = $executionContext;
			if ($isVar) {
				$innerContext = $innerContext->withAddedVariableValue(
					$this->target->variableName,
					$value
				);
			}
			return $executionContext->withValue(
				$this->onEmpty->execute($innerContext)->value
			);
		}
		if ($this->else !== null) {
			$innerContext = $executionContext;
			if ($isVar) {
				$innerContext = $innerContext->withAddedVariableValue(
					$this->target->variableName,
					$value
				);
			}
			return $executionContext->withValue(
				$this->else->execute($innerContext)->value
			);
		}
		return $executionContext;
	}

	public function __toString(): string {
		return sprintf("?whenIsEmpty (%s) { %s }%s",
			$this->target,
			$this->onEmpty,
			$this->else ? sprintf(" ~ { %s }", $this->else) : ''
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'MatchEmpty',
			'target' => $this->target,
			'onEmpty' => $this->onEmpty,
			'else' => $this->else
		];
	}
}