<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\Type;

interface MutableValue extends Value {
    public function type(): MutableType;
    public function targetType(): Type;
    public function value(): Value;
	public function changeValueTo(Value $value): void;
}