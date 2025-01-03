<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;

interface MethodCallExpression extends Expression {
	public Expression $target { get; }
	public MethodNameIdentifier $methodName { get; }
	public Expression $parameter { get; }
}