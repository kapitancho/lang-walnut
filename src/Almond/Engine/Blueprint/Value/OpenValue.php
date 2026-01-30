<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;

interface OpenValue extends Value {
	public OpenType $type { get; }
	public Value $value { get; }
}