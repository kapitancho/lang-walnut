<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Stringable;
use Walnut\Lang\Blueprint\Value\Value;

interface MatchExpressionOperation extends Stringable {
	public function match(Value $matchValue, Value $matchAgainst): bool;
}