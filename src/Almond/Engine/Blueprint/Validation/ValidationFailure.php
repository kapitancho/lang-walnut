<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Validation;

interface ValidationFailure extends ValidationResult {
	/** @var non-empty-list<ValidationError> $errors */
	public array $errors { get; }

	public function hasErrors(): true;
}