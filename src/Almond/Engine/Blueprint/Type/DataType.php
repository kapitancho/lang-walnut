<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface DataType extends NamedType {
	public Type $valueType { get; }
}