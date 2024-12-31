<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Type\Type;

interface MutableExpression extends Expression {
	public Type $type { get; }
	public Expression $value { get; }
}