<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\String\StringReverse;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class UnaryMinus implements NativeMethod {
	use StringReverse;
	use BaseType;
}