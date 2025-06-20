<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\DataType;

interface DataValue extends Value {
	public DataType $type { get; }
	public Value $value { get; }
}