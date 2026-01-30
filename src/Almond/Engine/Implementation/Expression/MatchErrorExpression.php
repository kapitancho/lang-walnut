<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue;

final readonly class MatchErrorExpression implements Expression {

	public function __construct(
		private TypeRegistry $typeRegistry,

		public Expression $target,
		public Expression $onError,
		public Expression|null $else,
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$anyType = $this->typeRegistry->any;

		$result = $this->target->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$expressionType = $result->expressionType; //TODO : to base type

		$returnTypes = [$result->returnType];
		$onErrorExpressionType = $this->typeRegistry->nothing;
		$elseExpressionType = $expressionType instanceof ResultType ? $expressionType->returnType : $anyType;

		if ($result->expressionType->isSubtypeOf(
			$this->typeRegistry->result(
				$anyType,
				$anyType
			)
		)) {
			$innerContext = $result;
			if ($this->target instanceof VariableNameExpression) {
				$errorType = $expressionType instanceof ResultType ? $expressionType->errorType :
					// @codeCoverageIgnoreStart
					$anyType;
					// @codeCoverageIgnoreEnd

				$innerContext = $innerContext->withAddedVariableType(
					$this->target->variableName,
					$this->typeRegistry->result(
						$this->typeRegistry->nothing,
						$errorType
					),
				);
			}
			$retValue = $this->onError->validateInContext($innerContext);
			if ($retValue instanceof ValidationFailure) {
				return $retValue;
			}

			$onErrorExpressionType = $retValue->expressionType;
			$returnTypes[] = $retValue->returnType;
		}
		if ($this->else) {
			$innerContext = $result;
			if ($this->target instanceof VariableNameExpression) {
				$innerContext = $innerContext->withAddedVariableType(
					$this->target->variableName,
					$elseExpressionType,
				);
			}
			$retValue = $this->else->validateInContext($innerContext);
			if ($retValue instanceof ValidationFailure) {
				return $retValue;
			}
			$elseExpressionType = $retValue->expressionType;
			$returnTypes[] = $retValue->returnType;
		}
		return $result
			->withExpressionType(
				$this->typeRegistry->union(
					[$onErrorExpressionType, $elseExpressionType]
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
			|> $this->onError->validateDependencies(...);
		if ($this->else !== null) {
			$dependencyContext = $this->else->validateDependencies($dependencyContext);
		}
		return $dependencyContext;
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$executionContext = $this->target->execute($executionContext);
		$value = $executionContext->value;
		if ($value instanceof ErrorValue) {
			$innerContext = $executionContext;
			if ($this->target instanceof VariableNameExpression) {
				$innerContext = $innerContext->withAddedVariableValue(
					$this->target->variableName,
					$value
				);
			}
			return $executionContext->withValue(
				$this->onError->execute($innerContext)->value
			);
		}
		if ($this->else !== null) {
			$innerContext = $executionContext;
			if ($this->target instanceof VariableNameExpression) {
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
		return sprintf("?whenIsError (%s) { %s }%s",
			$this->target,
			$this->onError,
			$this->else ? sprintf(" ~ { %s }", $this->else) : ''
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'MatchError',
			'target' => $this->target,
			'onError' => $this->onError,
			'else' => $this->else
		];
	}
}