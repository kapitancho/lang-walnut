<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\SubtypeType;

interface SubtypeValue extends Value {
	public SubtypeType $type { get; }
	public Value $baseValue { get; }
}