<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

interface UserlandTypeValidator {
	public function validateAll(): ValidationResult;
}