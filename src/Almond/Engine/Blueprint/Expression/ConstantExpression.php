<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface ConstantExpression extends Expression {
	public Value $value { get; }
}