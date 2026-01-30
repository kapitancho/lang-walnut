<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownProperty;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TupleType;

interface TupleValue extends Value {
	/** @var list<Value> */
	public array $values { get; }
	public TupleType $type { get; }

	public function valueOf(int $index): Value|UnknownProperty;
}