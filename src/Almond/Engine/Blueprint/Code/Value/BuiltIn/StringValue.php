<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface StringValue extends Value {
	public StringType $type { get; }
	//public StringSubsetType $type { get; }
	public string $literalValue { get; }
}