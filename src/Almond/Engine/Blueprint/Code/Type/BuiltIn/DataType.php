<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

interface DataType extends NamedType {
	public Type $valueType { get; }
}