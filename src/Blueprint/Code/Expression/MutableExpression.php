<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Type\Type;

interface MutableExpression extends Expression {
	public function type(): Type;
	public function value(): Expression;
}