<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface MutableValue extends Value {
	public Type $targetType { get; }
	public Value $value { get; set; }
}