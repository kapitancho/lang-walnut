<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Stringable;

interface MatchExpressionPair extends Stringable {
	public Expression $matchExpression { get; }
	public Expression $valueExpression { get; }
}