<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\SetType;

interface SetValue extends Value {
	/** @var list<Value> */
	public array $values { get; }
	/** @var array<string, Value> */
	public array $valueSet { get; }
	public SetType $type { get; }
}