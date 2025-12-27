<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayChunk;

final readonly class Chunk implements NativeMethod {
	use ArrayChunk;
}
