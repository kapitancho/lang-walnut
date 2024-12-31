<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\IntegerRange;

interface IntegerType extends Type {
	public IntegerRange $range { get; }
}