<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayPadLeftPadRight;

final readonly class PadRight implements NativeMethod {
	use ArrayPadLeftPadRight;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$programRegistry,
			$target,
			$parameter,
			1
		);
	}

}