<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\MapMergeWith;

final readonly class MergeWith implements NativeMethod {
	use MapMergeWith;
}