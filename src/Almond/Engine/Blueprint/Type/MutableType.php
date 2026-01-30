<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface MutableType extends Type {
	public Type $valueType { get; }
}