<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure as ValidationFailureInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess as ValidationSuccessInterface;

final readonly class ValidationSuccess implements ValidationSuccessInterface {

	public array $errors;
	public function __construct(public Type $type) {
		$this->errors = [];
	}

	public function ok(): ValidationSuccessInterface {
		return $this;
	}

	public function withError(ValidationErrorType $type, string $message, mixed $origin): ValidationFailureInterface {
		return $this->withValidationError(new ValidationError($type, $message, $origin));
	}

	public function withValidationError(ValidationErrorInterface $error): ValidationFailureInterface {
		return new ValidationFailure([...$this->errors, $error]);
	}

	public function hasErrors(): false { return false; }

	public function mergeFailure(ValidationFailureInterface $failure): ValidationFailureInterface {
		return $failure;
	}
}