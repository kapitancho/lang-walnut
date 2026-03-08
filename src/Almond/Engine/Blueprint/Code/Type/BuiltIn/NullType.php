<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;

interface NullType extends Type {
	public NullValue&AtomValue $value { get; }
}