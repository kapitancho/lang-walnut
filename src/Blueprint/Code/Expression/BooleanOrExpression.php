<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface BooleanOrExpression extends Expression {
	public Expression $first { get; }
	public Expression $second { get; }
}