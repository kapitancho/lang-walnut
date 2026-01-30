<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface VariableValue extends VariableType {
	public Value $value { get; }
}