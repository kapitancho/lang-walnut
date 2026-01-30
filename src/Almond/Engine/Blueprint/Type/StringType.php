<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;

interface StringType extends Type {
	public LengthRange $range { get; }
}