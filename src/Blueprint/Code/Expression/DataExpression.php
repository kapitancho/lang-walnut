<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface DataExpression extends Expression {
	public TypeNameIdentifier $typeName { get; }
	public Expression $value { get; }
}