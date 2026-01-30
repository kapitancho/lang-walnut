<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;

interface DataValue extends Value {
	public DataType $type { get; }
	public Value $value { get; }
}