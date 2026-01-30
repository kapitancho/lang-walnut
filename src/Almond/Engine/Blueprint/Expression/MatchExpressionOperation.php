<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Expression;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface MatchExpressionOperation extends Stringable {
	public function match(Value $matchValue, Value $matchAgainst): bool;
}