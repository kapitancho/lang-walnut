<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface ScopedExpression extends Expression {
	public Expression $targetExpression { get; }
}