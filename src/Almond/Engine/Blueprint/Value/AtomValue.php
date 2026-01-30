<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;

interface AtomValue extends Value {
	public AtomType $type { get; }
}