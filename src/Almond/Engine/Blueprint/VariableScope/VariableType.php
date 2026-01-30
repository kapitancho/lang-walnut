<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface VariableType {
	public VariableName $name { get; }
	public Type $type { get; }
}