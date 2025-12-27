<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Type\TypeIsSubtypeOf;

final readonly class BinaryLessThanEqual implements NativeMethod {
	use TypeIsSubtypeOf;
}