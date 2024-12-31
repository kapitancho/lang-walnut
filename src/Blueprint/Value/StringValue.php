<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\StringSubsetType;

interface StringValue extends LiteralValue {
	public StringSubsetType $type { get; }
	public string $literalValue { get; }
}