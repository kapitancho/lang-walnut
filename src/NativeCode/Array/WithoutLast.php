<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayWithoutFirstIWithoutLast;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithoutLast implements NativeMethod {
	use BaseType, ArrayWithoutFirstIWithoutLast;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$programRegistry,
			$target,
			$parameter,
			function(array $array) {
				$element = array_pop($array);
				return [$element, $array];
			}
		);
	}

}