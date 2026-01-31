<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Validation;

interface ValidationResult extends ValidationRequest {
	/** @var list<ValidationError> $errors */
	public array $errors { get; }

	public function mergeFailure(ValidationFailure $failure): ValidationFailure;

	public function hasErrors(): bool;
}