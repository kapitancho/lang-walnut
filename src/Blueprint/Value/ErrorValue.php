<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\ResultType;

interface ErrorValue extends Value {
	public ResultType $type { get; }
	public Value $errorValue { get; }
}