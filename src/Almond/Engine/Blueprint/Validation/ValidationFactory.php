<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScope;
use Walnut\Lang\Almond\Engine\Implementation\Validation\ValidationSuccess;

interface ValidationFactory {
	public ValidationResult $emptyValidationResult { get; }
	public ValidationContext $emptyValidationContext { get; }

	public function validationSuccess(Type $type): ValidationSuccess;
	public function fromVariableScope(VariableScope $variableScope): ValidationContext;

	public function error(ValidationErrorType $type, string $message, mixed $origin): ValidationFailure;
	public function validationError(ValidationError $error): ValidationFailure;
}