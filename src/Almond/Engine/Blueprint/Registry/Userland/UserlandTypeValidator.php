<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

interface UserlandTypeValidator {
	public function validateAll(): ValidationResult;
}