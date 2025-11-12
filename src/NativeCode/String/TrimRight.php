<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\String\StringTrimTrimLeftTrimRight;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class TrimRight implements NativeMethod {
	use BaseType, StringTrimTrimLeftTrimRight;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$programRegistry,
			$target,
			$parameter,
			rtrim(...)
		);
	}
}