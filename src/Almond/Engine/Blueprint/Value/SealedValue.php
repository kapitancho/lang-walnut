<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;

interface SealedValue extends Value {
	public SealedType $type { get; }
	public Value $value { get; }
}