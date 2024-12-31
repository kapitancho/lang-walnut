<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\NullType;

interface NullValue extends AtomValue, LiteralValue {
	public NullType $type { get; }
	public null $literalValue { get; }
}