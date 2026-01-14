<?php

namespace Walnut\Lang\Blueprint\Type;

interface AliasType extends NamedType, ComplexType {
	public Type $aliasedType { get; }
}