<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface TupleExpression extends Expression {
	/** @return list<Expression> */
	public function values(): array;
}