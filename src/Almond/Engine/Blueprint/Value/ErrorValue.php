<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;

interface ErrorValue extends Value {
	public ResultType $type { get; }
	public Value $errorValue { get; }
}