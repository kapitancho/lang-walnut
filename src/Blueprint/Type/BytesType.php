<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\LengthRange;

interface BytesType extends Type {
	public LengthRange $range { get; }
}