<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

interface ProgramValidator {
	public function validateProgram(): ValidationResult;
}