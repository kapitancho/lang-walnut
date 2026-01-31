<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;

interface ValidationFactory {
	public ValidationResult $emptyValidationResult { get; }
	public ValidationContext $emptyValidationContext { get; }

	public function validationSuccess(Type $type): ValidationSuccess;
	public function fromVariableScope(VariableScope $variableScope): ValidationContext;

	public function error(ValidationErrorType $type, string $message, mixed $origin): ValidationFailure;
	public function validationError(ValidationError $error): ValidationFailure;
}