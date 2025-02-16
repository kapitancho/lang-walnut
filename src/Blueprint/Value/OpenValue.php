<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\OpenType;

interface OpenValue extends CustomValue {
	public OpenType $type { get; }
}