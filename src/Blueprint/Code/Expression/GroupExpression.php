<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface GroupExpression extends Expression {
	public Expression $innerExpression { get; }
}