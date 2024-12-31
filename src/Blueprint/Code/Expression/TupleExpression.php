<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface TupleExpression extends Expression {
	/** @param list<Expression> $values */
	public array $values { get; }
}