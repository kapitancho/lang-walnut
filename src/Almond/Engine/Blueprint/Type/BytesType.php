<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;

interface BytesType extends Type {
	public LengthRange $range { get; }
}
