<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface BooleanNotExpression extends Expression {
	public Expression $expression { get; }
}