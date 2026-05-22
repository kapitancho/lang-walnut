<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\ExternalTypeHelper;

final readonly class MatchExternalErrorExpression implements Expression, JsonSerializable {
	use BaseType;
	use ExternalTypeHelper;

	public function __construct(
		private TypeRegistry $typeRegistry,

		public Expression $target,
		public Expression $onError,
		public Expression|null $else,
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->target->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$expressionType = $this->toBaseType($result->expressionType);

		$returnTypes = [$result->returnType];

		$isOptional = false;
		if ($expressionType instanceof OptionalType) {
			$isOptional = true;
			$expressionType = $expressionType->valueType;
		}

		$elseExpressionType = $expressionType instanceof AnyType || $expressionType instanceof ResultType ?
			$this->withoutExternalError($this->typeRegistry, $expressionType) : $expressionType;

		$externalErrorType = $this->typeRegistry->userland->sealed(
			CoreType::ExternalError->typeName()
		);

		$onErrorExpressionType = match(true) {
			$expressionType instanceof AnyType => $expressionType,
			$expressionType instanceof ResultType && $externalErrorType->isSubtypeOf(
				$expressionType->errorType
			) => $externalErrorType,
			default => $this->typeRegistry->nothing
		};
		if ($isOptional) {
			$elseExpressionType = $this->typeRegistry->optional($elseExpressionType);
		}

		$innerContext = $result;
		$isVar = $this->target instanceof VariableNameExpression || $this->target instanceof VariableAssignmentExpression;
		if ($isVar) {
			$innerContext = $innerContext->withAddedVariableType(
				$this->target->variableName,
				$this->typeRegistry->error(
					$onErrorExpressionType
				),
			);
		}
		$retValue = $this->onError->validateInContext($innerContext);
		if ($retValue instanceof ValidationFailure) {
			return $retValue;
		}
		if (!$onErrorExpressionType instanceof NothingType) {
			$onErrorExpressionType = $retValue->expressionType;
			$returnTypes[] = $retValue->returnType;
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
		$isVar = $this->target instanceof VariableNameExpression || $this->target instanceof VariableAssignmentExpression;
		if ($value instanceof ErrorValue) {
			$errorValue = $value->errorValue;
			if ($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				CoreType::ExternalError->typeName()
			)) {
				$innerContext = $executionContext;
				if ($isVar) {
					$innerContext = $innerContext->withAddedVariableValue(
						$this->target->variableName,
						$value
					);
				}
				return $executionContext->withValue(
					$this->onError->execute($innerContext)->value
				);
			}
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
		return sprintf("?whenIsExternalError (%s) { %s }%s",
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