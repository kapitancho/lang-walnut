<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface TypeValue extends Value {
	public Type $typeValue { get; }
}