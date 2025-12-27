<?php

namespace Walnut\Lang\NativeCode\ByteArray;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\ByteArray\ByteArrayReverse;

final readonly class UnaryMinus implements NativeMethod {
	use ByteArrayReverse;
}
