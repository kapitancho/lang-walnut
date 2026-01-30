<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface OpenType extends NamedType {
	public Type $valueType { get; }
}