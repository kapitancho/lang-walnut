<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\String\StringPositionOfLastPositionOf;

final readonly class LastPositionOf implements NativeMethod {
	use StringPositionOfLastPositionOf;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$programRegistry,
			$target,
			$parameter,
			strrpos(...)
		);
	}

}