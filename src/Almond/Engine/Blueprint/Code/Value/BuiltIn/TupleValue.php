<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface TupleValue extends Value {
	/** @var list<Value> */
	public array $values { get; }
	public TupleType $type { get; }

	public function valueOf(int $index): Value|UnknownProperty;
}