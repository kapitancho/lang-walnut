<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface ConstantExpression extends Expression {
	public Value $value { get; }
}