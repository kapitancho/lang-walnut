<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\CustomType;

interface CustomValue extends Value {
	public CustomType $type { get; }
	public Value $value { get; }
}