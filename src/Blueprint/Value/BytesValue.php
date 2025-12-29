<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\BytesType;

interface BytesValue extends LiteralValue {
	public BytesType $type { get; }
	public string $literalValue { get; }
}