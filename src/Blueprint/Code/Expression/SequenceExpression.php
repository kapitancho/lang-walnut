<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface SequenceExpression extends Expression {
	/** @return list<Expression> */
	public function expressions(): array;
}