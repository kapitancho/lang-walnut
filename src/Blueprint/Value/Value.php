<?php

namespace Walnut\Lang\Blueprint\Value;

use Stringable;
use Walnut\Lang\Blueprint\Type\Type;

interface Value extends Stringable {
	public Type $type { get; }
	public function equals(Value $other): bool;
}