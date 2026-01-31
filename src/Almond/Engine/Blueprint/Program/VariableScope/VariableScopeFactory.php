<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope;

interface VariableScopeFactory {
	public VariableScope $emptyVariableScope { get; }
	public VariableValueScope $emptyVariableValueScope { get; }
}