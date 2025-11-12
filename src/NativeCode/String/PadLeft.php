<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\String\StringPadLeftPadRight;

final readonly class PadLeft implements NativeMethod {
	use StringPadLeftPadRight;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$programRegistry,
			$target,
			$parameter,
			STR_PAD_LEFT
		);
	}

}