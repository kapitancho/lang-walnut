<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Stringable;

interface MatchExpressionPair extends Stringable {
	public function matchExpression(): Expression;
	public function valueExpression(): Expression;
}