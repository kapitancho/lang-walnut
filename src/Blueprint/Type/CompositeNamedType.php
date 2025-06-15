<?php

namespace Walnut\Lang\Blueprint\Type;

interface CompositeNamedType extends NamedType {
	public Type $valueType { get; }
}