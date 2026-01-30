<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Expression;

use Stringable;

interface MatchExpressionDefault extends Stringable {
	public Expression $valueExpression { get; }
}