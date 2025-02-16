<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\SealedType;

interface SealedValue extends CustomValue {
	public SealedType $type { get; }
}