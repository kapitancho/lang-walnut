<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Mutable\MutablePushUnshift;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class PUSH implements NativeMethod {
	use BaseType, MutablePushUnshift;

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$programRegistry,
			$target,
			$parameter,
			function(array $arr, Value $parameter): array {
				$arr[] = $parameter;
				return $arr;
			}
		);
	}

}