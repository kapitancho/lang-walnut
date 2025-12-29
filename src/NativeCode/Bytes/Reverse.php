<?php

namespace Walnut\Lang\NativeCode\Bytes;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Bytes\BytesReverse;

final readonly class Reverse implements NativeMethod {
	use BytesReverse;
}
