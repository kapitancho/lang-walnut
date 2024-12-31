<?php

namespace Walnut\Lang\Blueprint\Type;

interface AliasType extends NamedType {
	public Type $aliasedType { get; }
}