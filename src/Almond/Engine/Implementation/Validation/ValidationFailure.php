<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure as ValidationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;

final readonly class ValidationFailure implements ValidationFailureInterface {

	/**
	 * @param list<ValidationErrorInterface> $errors
	 */
	public function __construct(public array $errors) {}

	public function ok(): ValidationFailureInterface {
		return $this;
	}

	public function mergeWith(ValidationResult $other): ValidationFailureInterface {
		return new ValidationFailure(
			array_merge($this->errors, $other->errors)
		);
	}

	public function withError(ValidationErrorType $type, string $message, mixed $origin): ValidationFailureInterface {
		return $this->withValidationError(new ValidationError($type, $message, $origin));
	}

	public function withValidationError(ValidationErrorInterface $error): ValidationFailureInterface {
		return new ValidationFailure([...$this->errors, $error]);
	}

	public function hasErrors(): true { return true; }

	public function mergeFailure(ValidationFailureInterface $failure): ValidationFailureInterface {
		return new ValidationFailure(
			array_merge($this->errors, $failure->errors)
		);
	}
}