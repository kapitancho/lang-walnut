<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\LengthRange;

interface StringType extends Type {
	public LengthRange $range { get; }
}