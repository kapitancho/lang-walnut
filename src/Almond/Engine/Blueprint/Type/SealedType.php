<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface SealedType extends NamedType {
	public Type $valueType { get; }
}