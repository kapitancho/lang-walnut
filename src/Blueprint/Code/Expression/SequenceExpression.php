<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface SequenceExpression extends Expression {
	/** @param list<Expression> $expressions */
	public array $expressions { get; }
}