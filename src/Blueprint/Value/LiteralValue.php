<?php

namespace Walnut\Lang\Blueprint\Value;

use BcMath\Number;

interface LiteralValue extends Value {
	public Number|string|bool|null $literalValue { get; }
}