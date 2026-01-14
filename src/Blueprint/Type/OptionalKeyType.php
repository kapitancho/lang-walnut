<?php

namespace Walnut\Lang\Blueprint\Type;

interface OptionalKeyType extends CompositeType {
	public Type $valueType { get; }
}