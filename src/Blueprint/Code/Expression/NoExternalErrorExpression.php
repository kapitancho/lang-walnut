<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

interface NoExternalErrorExpression extends Expression {
	public Expression $targetExpression { get; }
}