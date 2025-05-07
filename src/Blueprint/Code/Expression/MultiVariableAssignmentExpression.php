<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface MultiVariableAssignmentExpression extends Expression {
	/** @param array<VariableNameIdentifier> $variableNames */
	public array $variableNames { get; }
	public Expression $assignedExpression { get; }
}