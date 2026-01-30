<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext as ValidationContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure as ValidationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScope;

final readonly class ValidationContext implements ValidationContextInterface {
	public array $errors;
	public function __construct(
		public VariableScope $variableScope,
		public Type $expressionType,
		public Type $returnType
	) {
		$this->errors = [];
	}

	public function ok(): ValidationContextInterface {
		return $this;
	}
	public function withAddedVariableTypes(iterable $types): ValidationContext {
		return clone($this, ['variableScope' => $this->variableScope->withAddedVariableTypes($types)]);
	}
	public function withAddedVariableType(VariableName $name, Type $type): ValidationContext {
		return clone($this, ['variableScope' => $this->variableScope->withAddedVariableType($name, $type)]);
	}
	public function withExpressionType(Type $type): ValidationContext {
		return clone($this, ['expressionType' => $type]);
	}
	public function withReturnType(Type $type): ValidationContext {
		return clone($this, ['returnType' => $type]);
	}

	public function withError(ValidationErrorType $type, string $message, mixed $origin): ValidationFailureInterface {
		return $this->withValidationError(new ValidationError($type, $message, $origin));
	}
	public function withValidationError(ValidationErrorInterface $error): ValidationFailureInterface {
		return new ValidationFailure([$error]);
	}

	public function hasErrors(): false { return false; }

	public function mergeFailure(ValidationFailureInterface $failure): ValidationFailureInterface {
		return $failure;
	}
}