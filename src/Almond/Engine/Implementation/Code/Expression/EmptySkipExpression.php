<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\EmptySkipTargetExpression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEmptySkip;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class EmptySkipExpression implements Expression, JsonSerializable {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private string $skipTargetId,
		private Expression $targetExpression,
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		/*$skipTargetResult = $this->skipTarget->validateInContext($validationContext);
		if ($skipTargetResult instanceof ValidationFailure) {
			return $skipTargetResult;
		}*/

		$result = $this->targetExpression->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}

		$expressionType = $result->expressionType;
		if (1 || $skipTargetResult->expressionType instanceof OptionalType) {
			$expressionType = $this->typeRegistry->optional($expressionType);
		}
		return $result->withExpressionType($expressionType);
	}

	public function isScopeSafe(): bool {
		return $this->targetExpression->isScopeSafe();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->targetExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		try {
			$result = $this->targetExpression->execute($executionContext);
		} catch (ExecutionEmptySkip $exception) {
			if ($exception->skipTargetId === $this->skipTargetId) {
				return $exception->executionContext;
			}
			throw $exception;
		}
		return $result;
	}

	public function __toString(): string {
		return $this->targetExpression;
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'EmptySkip',
			'targetExpression' => $this->targetExpression
		];
	}
}
