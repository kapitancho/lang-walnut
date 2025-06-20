<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;

interface MutableValue extends Value {
	public MutableType $type { get; }
	public Type $targetType { get; }
	public Value $value { get; set; }
}