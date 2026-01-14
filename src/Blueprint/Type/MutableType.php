<?php

namespace Walnut\Lang\Blueprint\Type;

interface MutableType extends CompositeType {
	public Type $valueType { get; }
}