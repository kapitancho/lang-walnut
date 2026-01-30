<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Value\AtomValue;

interface AtomType extends NamedType {
	public AtomValue $value { get; }
}