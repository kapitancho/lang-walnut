<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure as ValidationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult as ValidationResultInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;

final readonly class ValidationResult implements ValidationResultInterface {

	/** @param list<ValidationErrorInterface> $errors */
	public function __construct(public array $errors) {}

	public function ok(): ValidationResultInterface {
		return $this;
	}

	public function mergeFailure(ValidationFailureInterface $failure): ValidationFailureInterface {
		return new ValidationFailure([...$this->errors, ...$failure->errors]);
	}

	public function withError(ValidationErrorType $type, string $message, mixed $origin): ValidationFailureInterface {
		return $this->withValidationError(new ValidationError($type, $message, $origin));
	}

	public function withValidationError(ValidationErrorInterface $error): ValidationFailureInterface {
		return new ValidationFailure([...$this->errors, $error]);
	}

	public function hasErrors(): bool {
		return count($this->errors) > 0;
	}

}