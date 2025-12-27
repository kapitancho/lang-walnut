<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayReverse;

final readonly class UnaryMinus implements NativeMethod {
	use ArrayReverse;
}