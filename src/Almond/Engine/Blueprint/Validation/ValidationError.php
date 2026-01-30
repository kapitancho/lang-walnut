<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Validation;

interface ValidationError {
	public ValidationErrorType $type { get; }
	public string $message { get; }
	public mixed $origin { get; }
}