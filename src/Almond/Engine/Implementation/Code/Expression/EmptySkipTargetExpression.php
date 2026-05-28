<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEmptySkip;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class EmptySkipTargetExpression implements Expression, JsonSerializable {

	public function __construct(
		public string $skipTargetId,
		public Expression $targetExpression,
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->targetExpression->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$expressionType = $result->expressionType;
		if ($expressionType instanceof OptionalType) {
			$expressionType = $expressionType->valueType;
		} else {
			return $validationContext->withError(
				ValidationErrorType::typeTypeMismatch,
				sprintf(
					"Expression of type '%s' cannot be empty'.",
					$expressionType,
				),
				$this->targetExpression
			);
		}
		return $result->withExpressionType($expressionType);
	}

	public function isScopeSafe(): bool {
		return $this->targetExpression->isScopeSafe();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->targetExpression->validateDependencies($dependencyContext);
	}

	/** @throws ExecutionEarlyReturn|ExecutionEmptySkip|ExecutionException */
	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$result = $this->targetExpression->execute($executionContext);
		if ($result->value instanceof EmptyValue) {
			throw new ExecutionEmptySkip($this->skipTargetId, $result);
		}
		return $result;
	}

	public function __toString(): string {
		return sprintf(
			"%s?",
			$this->targetExpression,
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'EmptySkipTarget',
			'targetExpression' => $this->targetExpression
		];
	}
}
