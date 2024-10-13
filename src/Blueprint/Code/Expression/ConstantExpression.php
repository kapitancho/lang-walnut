<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Value\Value;

interface ConstantExpression extends Expression {
	public function value(): Value;
}