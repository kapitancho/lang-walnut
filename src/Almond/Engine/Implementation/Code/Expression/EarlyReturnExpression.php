<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\EarlyReturnExpressionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\ExternalTypeHelper;

final readonly class EarlyReturnExpression implements Expression, JsonSerializable {
	use ExternalTypeHelper;

	public function __construct(
		private TypeRegistry $typeRegistry,
		private Expression $targetExpression,
		private EarlyReturnExpressionType $type
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->targetExpression->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$expressionType = $result->expressionType;
		$hasOptional = false;
		if ($expressionType instanceof OptionalType) {
			$hasOptional = true;
			$expressionType = $expressionType->valueType;
		}
		[$resultType, $errorType] = match(true) {
			$expressionType instanceof ResultType => [$expressionType->returnType, $expressionType->errorType],
			$expressionType instanceof AnyType => [$expressionType, $expressionType],
			default => [$expressionType, $this->typeRegistry->nothing]
		};

		if ($this->type === EarlyReturnExpressionType::onEmpty) {
			$eType = $expressionType;
			$rType = $hasOptional ? $this->typeRegistry->empty : $this->typeRegistry->nothing;
		} elseif ($this->type === EarlyReturnExpressionType::onError) {
			$eType = $hasOptional ? $this->typeRegistry->optional($resultType) : $resultType;
			$rType = $this->typeRegistry->error($errorType);
		} elseif ($this->type === EarlyReturnExpressionType::onExternalError) {
			$externalErrorType = $this->typeRegistry->userland->sealed(CoreType::ExternalError->typeName());
			if ($externalErrorType->isSubtypeOf($errorType)) {
				$wex = $this->withoutExternalError($this->typeRegistry, $expressionType);
				$eType = $hasOptional ? $this->typeRegistry->optional($wex) : $wex;
				$rType = $this->typeRegistry->error($externalErrorType);
			} else {
				$eType = $result->expressionType;
				$rType = $this->typeRegistry->nothing;
			}
		} else /*if ($this->type === EarlyReturnExpressionType::onEmptyAndError)*/ {
			$eType = $resultType;
			$rType = $this->typeRegistry->error($errorType);
			$rType = $hasOptional ? $this->typeRegistry->optional($rType) : $rType;
		}
		return $result
			->withExpressionType($eType)
			->withReturnType($rType);
	}

	public function isScopeSafe(): bool {
		return $this->targetExpression->isScopeSafe();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->targetExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$result = $this->targetExpression->execute($executionContext);
		$checks = [];
		if ($result->value instanceof EmptyValue) {
			$checks = [
				EarlyReturnExpressionType::onEmpty,
				EarlyReturnExpressionType::onEmptyAndError
			];
		} elseif ($result->value instanceof ErrorValue) {
			$checks = [
				EarlyReturnExpressionType::onError,
				EarlyReturnExpressionType::onEmptyAndError
			];
			$errorValue = $result->value->errorValue;
			if ($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				CoreType::ExternalError->typeName()
			)) {
				$checks[] = EarlyReturnExpressionType::onExternalError;
			}
		}
		if (in_array($this->type, $checks, true)) {
			throw new ExecutionEarlyReturn($result->value);
		}
		return $result;
	}

	public function __toString(): string {
		return sprintf(
			"(%s)%s",
			$this->targetExpression,
			$this->type->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'EarlyReturn',
			'type' => $this->type->name,
			'targetExpression' => $this->targetExpression
		];
	}
}
