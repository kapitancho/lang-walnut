<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\IntegerRange;

interface IntegerType extends Type {
	public IntegerRange $range { get; }
}