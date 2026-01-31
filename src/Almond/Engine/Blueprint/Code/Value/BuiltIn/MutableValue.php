<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface MutableValue extends Value {
	public Type $targetType { get; }
	public Value $value { get; set; }
}