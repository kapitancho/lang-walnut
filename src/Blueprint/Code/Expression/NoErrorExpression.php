<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface NoErrorExpression extends Expression {
	public function targetExpression(): Expression;
}