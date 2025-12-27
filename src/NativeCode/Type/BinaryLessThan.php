<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Type\TypeIsSubtypeOf;

final readonly class BinaryLessThan implements NativeMethod {
	use TypeIsSubtypeOf;

	private function checker(Type $target, Type $parameter): bool {
		return $target->isSubtypeOf($parameter) && !$parameter->isSubtypeOf($target);
	}

}