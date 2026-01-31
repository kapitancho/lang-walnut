<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

use Stringable;

interface MatchExpressionDefault extends Stringable {
	public Expression $valueExpression { get; }
}