<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\LengthRange;

interface StringType extends SimpleType {
	public LengthRange $range { get; }
}