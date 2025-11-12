<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayAppendWith;

final readonly class BinaryPlus implements NativeMethod {
	use ArrayAppendWith;
}