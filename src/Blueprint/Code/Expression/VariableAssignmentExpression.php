<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;

interface VariableAssignmentExpression extends Expression {
	public function variableName(): VariableNameIdentifier;
	public function assignedExpression(): Expression;
}