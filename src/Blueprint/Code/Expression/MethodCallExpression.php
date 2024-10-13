<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;

interface MethodCallExpression extends Expression {
	public function target(): Expression;
	public function methodName(): MethodNameIdentifier;
	public function parameter(): Expression;
}