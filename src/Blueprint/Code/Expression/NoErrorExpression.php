<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface NoErrorExpression extends Expression {
	public Expression $targetExpression { get; }
}