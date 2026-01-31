<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface VariableValue extends VariableType {
	public Value $value { get; }
}