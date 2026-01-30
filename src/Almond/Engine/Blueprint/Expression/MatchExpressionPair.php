<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Expression;

use Stringable;

interface MatchExpressionPair extends Stringable {
	public Expression $matchExpression { get; }
	public Expression $valueExpression { get; }
}