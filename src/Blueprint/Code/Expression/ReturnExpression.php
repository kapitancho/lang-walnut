<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface ReturnExpression extends Expression {
	public function returnedExpression(): Expression;
}