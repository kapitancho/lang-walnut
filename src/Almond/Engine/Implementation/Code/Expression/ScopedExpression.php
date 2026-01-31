<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class ScopedExpression implements Expression, JsonSerializable {

	public function __construct(
		private TypeRegistry $typeRegistry,

		private Expression $targetExpression
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->targetExpression->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		$expressionType = $result->expressionType;
		$returnType = $result->returnType;
		return $validationContext->withExpressionType(
			$returnType instanceof NothingType ? $expressionType :
				$this->typeRegistry->union([
					$expressionType,
					$returnType
				])
		)->withReturnType($this->typeRegistry->nothing);
	}

	// Scoped expressions are always scope safe because they create a new scope.
	public function isScopeSafe(): bool { return true; }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->targetExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		try {
			$result = $this->targetExpression->execute($executionContext)->value;
		} catch (ExecutionEarlyReturn $return) {
			$result = $return->returnValue;
		}
		return $executionContext->withValue($result);
	}

	public function __toString(): string {
		return sprintf(
			":: %s",
			$this->targetExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Scoped',
			'targetExpression' => $this->targetExpression
		];
	}
}