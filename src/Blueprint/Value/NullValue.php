<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\NullType;

interface NullValue extends AtomValue, LiteralValue {
	public function type(): NullType;
    public function literalValue(): null;
}