<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayWithoutAll;

final readonly class BinaryMinus implements NativeMethod {
	use ArrayWithoutAll;
}