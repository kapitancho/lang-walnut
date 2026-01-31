<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

interface VariableType {
	public VariableName $name { get; }
	public Type $type { get; }
}