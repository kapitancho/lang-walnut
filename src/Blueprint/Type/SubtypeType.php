<?php

namespace Walnut\Lang\Blueprint\Type;

interface SubtypeType extends NamedType {
	public Type $baseType { get; }
}