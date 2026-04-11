<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext as ValidationContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure as ValidationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResultCollector;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;

final readonly class ValidationContext implements ValidationContextInterface {
	public array $errors;
	public function __construct(
		private ValidationResultCollector $validationResultCollector,
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
		$result = clone($this, ['variableScope' => $this->variableScope->withAddedVariableTypes($types)]);
		$this->validationResultCollector->collect($result);
		return $result;
	}
	public function withAddedVariableType(VariableName $name, Type $type): ValidationContext {
		return clone($this, ['variableScope' => $this->variableScope->withAddedVariableType($name, $type)]);
	}
	public function withExpressionType(Type $type): ValidationContext {
		$result = clone($this, ['expressionType' => $type]);
		$this->validationResultCollector->collect($result);
		return $result;
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