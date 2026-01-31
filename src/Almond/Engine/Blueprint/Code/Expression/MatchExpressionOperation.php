<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

use Stringable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface MatchExpressionOperation extends Stringable {
	public function match(Value $matchValue, Value $matchAgainst): bool;
}