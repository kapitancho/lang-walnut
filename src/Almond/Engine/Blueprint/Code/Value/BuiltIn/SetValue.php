<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface SetValue extends Value {
	/** @var list<Value> */
	public array $values { get; }
	/** @var array<string, Value> */
	public array $valueSet { get; }
	public SetType $type { get; }
}