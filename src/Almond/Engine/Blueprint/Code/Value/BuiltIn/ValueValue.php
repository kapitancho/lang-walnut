<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface ValueValue extends Value {
	public ValueType $type { get; }
	public Value $value { get; }
}
