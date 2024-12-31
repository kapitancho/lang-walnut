<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;

interface VariableNameExpression extends Expression {
	public VariableNameIdentifier $variableName { get; }
}