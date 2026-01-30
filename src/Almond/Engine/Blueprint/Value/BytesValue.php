<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\BytesType;

interface BytesValue extends Value {
	public BytesType $type { get; }
	public string $literalValue { get; }
}
