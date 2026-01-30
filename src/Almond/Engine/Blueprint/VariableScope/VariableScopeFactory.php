<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\VariableScope;

interface VariableScopeFactory {
	public VariableScope $emptyVariableScope { get; }
	public VariableValueScope $emptyVariableValueScope { get; }
}