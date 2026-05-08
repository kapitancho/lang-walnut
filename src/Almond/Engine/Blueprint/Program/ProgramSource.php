<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

interface ProgramSource {
	public function asProgram(): Program|ValidationResult;
}