<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;

final readonly class DataExpression implements Expression {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private ValidationFactory $validationFactory,

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
		return sprintf("(%s)", $this->value);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'group',
			'innerExpression' => $this->value
		];
	}
}