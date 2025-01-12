<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\SetType;

interface SetValue extends Value {
	/** @var list<Value> */
	public array $values { get; }
	/** @var array<string, Value> */
	public array $valueSet { get; }
	public SetType $type { get; }
}