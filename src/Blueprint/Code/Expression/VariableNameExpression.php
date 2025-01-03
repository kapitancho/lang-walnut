<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface VariableNameExpression extends Expression {
	public VariableNameIdentifier $variableName { get; }
}