<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\StringType;

interface StringValue extends Value {
	public StringType $type { get; }
	//public StringSubsetType $type { get; }
	public string $literalValue { get; }
}