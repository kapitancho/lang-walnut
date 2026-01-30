<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError as ValidationErrorInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;

final readonly class ValidationError implements ValidationErrorInterface {
	public function __construct(
		public ValidationErrorType $type,
		public string              $message,
		public mixed               $origin
	) {}
}