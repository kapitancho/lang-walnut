<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class MutableExpression implements Expression, JsonSerializable {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,

		public Type $type,
		public Expression $value
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$result = $this->value->validateInContext($validationContext);
		if ($result instanceof ValidationFailure) {
			return $result;
		}
		if (!$result->expressionType->isSubtypeOf($this->type)) {
			return $result->withError(
				ValidationErrorType::mutableTypeMismatch,
				sprintf(
					"Mutable expression value type %s is not a subtype of %s",
					$result->expressionType,
					$this->type
				),
				$this
			);
		}
		return $result->withExpressionType(
			$this->typeRegistry->mutable($this->type)
		);
	}

	public function isScopeSafe(): bool { return $this->value->isScopeSafe(); }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->value->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$result = $this->value->execute($executionContext);
		if (!$result->value->type->isSubtypeOf($this->type)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf(
					"Mutable expression type %s is not a subtype of %s",
					$result->value->type,
					$this->type
				)
			);
			// @codeCoverageIgnoreEnd
		}
		return $result->withValue(
			$this->valueRegistry->mutable(
				$this->type,
				$result->value
			)
		);
	}


	public function __toString(): string {
		return sprintf(
			"mutable{%s, %s}",
			$this->type,
			$this->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Mutable',
			'type' => $this->type,
			'value' => $this->value
		];
	}
}