<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\UnknownProperty;

interface TupleValue extends Value {
	/** @var list<Value> */
	public array $values { get; }
	public TupleType $type { get; }

	/** @throws UnknownProperty */
	public function valueOf(int $index): Value;
}