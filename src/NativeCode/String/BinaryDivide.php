<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\String\StringChunk;

final readonly class BinaryDivide implements NativeMethod {
	use StringChunk;
}