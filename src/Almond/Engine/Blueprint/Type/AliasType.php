<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface AliasType extends NamedType {
	public Type $aliasedType { get; }
}