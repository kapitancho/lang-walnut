<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface DirectExpression extends Expression {
	public Expression $targetExpression { get; }
}