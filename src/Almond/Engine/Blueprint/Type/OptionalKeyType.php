<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface OptionalKeyType extends Type {
	public Type $valueType { get; }
}