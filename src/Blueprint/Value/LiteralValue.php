<?php

namespace Walnut\Lang\Blueprint\Value;

interface LiteralValue extends Value {
	public int|float|string|bool|null $literalValue { get; }
}