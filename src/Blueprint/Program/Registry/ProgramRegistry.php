<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;

interface ProgramRegistry {
	public TypeRegistry $typeRegistry { get; }
	public ValueRegistry $valueRegistry { get; }
	public ExpressionRegistry $expressionRegistry { get; }
	public VariableValueScope $globalScope { get; }
}