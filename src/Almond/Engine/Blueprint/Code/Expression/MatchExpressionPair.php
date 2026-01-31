<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

use Stringable;

interface MatchExpressionPair extends Stringable {
	public Expression $matchExpression { get; }
	public Expression $valueExpression { get; }
}