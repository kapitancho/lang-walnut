<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Validation;

interface ValidationRequest {
	public function ok(): ValidationResult;
	public function withError(ValidationErrorType $type, string $message, mixed $origin): ValidationFailure;
	public function withValidationError(ValidationError $error): ValidationFailure;
}