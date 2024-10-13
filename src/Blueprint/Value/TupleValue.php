<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\UnknownProperty;

interface TupleValue extends Value {
    /** @return list<Value> */
    public function values(): array;
    public function type(): TupleType;
	/** @throws UnknownProperty */
	public function valueOf(int $index): Value;
}