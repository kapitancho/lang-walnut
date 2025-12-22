<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\ByteArrayType;

interface ByteArrayValue extends LiteralValue {
	public ByteArrayType $type { get; }
	public string $literalValue { get; }
}