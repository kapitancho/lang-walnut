<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\LengthRange;

interface BytesType extends SimpleType {
	public LengthRange $range { get; }
}