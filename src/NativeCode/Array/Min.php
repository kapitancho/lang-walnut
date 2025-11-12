<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayMinMax;

final readonly class Min implements NativeMethod {
	use ArrayMinMax;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$target,
			$parameter,
			fn($value, $best): bool => $value < $best
		);
	}

}