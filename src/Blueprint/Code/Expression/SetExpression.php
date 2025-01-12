<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface SetExpression extends Expression {
	/** @param list<Expression> $values */
	public array $values { get; }
}