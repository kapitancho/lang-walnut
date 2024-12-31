<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Stringable;

interface MatchExpressionDefault extends Stringable {
	public Expression $valueExpression { get; }
}