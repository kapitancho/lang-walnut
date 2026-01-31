<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\ExternalTypeHelper;
use Walnut\Lang\Almond\Engine\Implementation\Expression\CoreType;
use Walnut\Lang\Almond\Engine\Implementation\Expression\SealedValue;

final readonly class NoExternalErrorExpression implements Expression, JsonSerializable {
	use ExternalTypeHelper;

	public function __construct(
		private TypeRegistry $typeRegistry,
		private Expression $targetExpression) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->targetExpression->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$expressionType = $result->expressionType;
		$externalErrorType = $this->typeRegistry->typeByName(new TypeName('ExternalError'));
		if ($expressionType instanceof ResultType && $externalErrorType->isSubtypeOf($expressionType->errorType)) {
			return $result->withExpressionType(
				$this->withoutExternalError($this->typeRegistry, $expressionType)
			)->withReturnType(
				$this->typeRegistry->result(
					$result->returnType,
					$externalErrorType
				)
			);
		}
		return $result;
	}

	public function isScopeSafe(): bool { return $this->targetExpression->isScopeSafe(); }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->targetExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$result = $this->targetExpression->execute($executionContext);
		if ($result->value instanceof ErrorValue) {
			$errorValue = $result->value->errorValue;
			if ($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				CoreType::ExternalError->typeName()
			)) {
				throw new ExecutionEarlyReturn($result->value);
			}
		}
		return $result;
	}

	public function __toString(): string {
		return sprintf(
			"(%s)*?",
			$this->targetExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'NoExternalError',
			'targetExpression' => $this->targetExpression
		];
	}
}