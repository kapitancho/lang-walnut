<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\String\StringReverse;

final readonly class Reverse implements NativeMethod {
	use StringReverse;
}