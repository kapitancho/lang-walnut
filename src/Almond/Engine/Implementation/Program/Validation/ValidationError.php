<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;

final readonly class ValidationError implements ValidationErrorInterface {
	public function __construct(
		public ValidationErrorType $type,
		public string              $message,
		public mixed               $origin
	) {}
}