<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\AtomType;

interface AtomValue extends Value {
	public AtomType $type { get; }
}