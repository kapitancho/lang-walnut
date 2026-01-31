<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class DataExpression implements Expression, JsonSerializable {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,

		private TypeName $typeName,
		private Expression $value
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$type = $this->typeRegistry->userland->data($this->typeName);

		$validationContext = $this->value->validateInContext($validationContext);
		if ($validationContext instanceof ValidationFailure) {
			return $validationContext;
		}
		if (!$validationContext->expressionType->isSubtypeOf($type->valueType)) {
			return $validationContext->withError(
				ValidationErrorType::typeTypeMismatch,
				sprintf(
					"Expression of type '%s' is not compatible with expected type '%s'.",
					$validationContext->expressionType,
					$type
				),
				$this
			);
		}
		return $validationContext->withExpressionType($type);
	}

	public function isScopeSafe(): bool { return $this->value->isScopeSafe(); }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->value->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$type = $this->typeRegistry->typeByName($this->typeName);
		if (!$type instanceof DataType) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf("The type '%s' is not a data type.", $this->typeName)
			);
			// @codeCoverageIgnoreEnd
		}
		$executionContext = $this->value->execute($executionContext);
		if (!$executionContext->value->type->isSubtypeOf($type->valueType)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf(
					"The data type '%s' expected base value of type '%s', but got '%s'.",
					$this->typeName,
					$type->valueType,
					$executionContext->value->type
				)
			);
			// @codeCoverageIgnoreEnd
		}
		$value = $this->valueRegistry->data($type->name, $executionContext->value);
		return $executionContext->withValue($value);
	}

	public function __toString(): string {
		return sprintf(
			"%s!%s",
			$this->typeName,
			$this->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Data',
			'typeName' => $this->typeName,
			'value' => $this->value
		];
	}
}