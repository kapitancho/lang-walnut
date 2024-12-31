<?php

namespace Walnut\Lang\Blueprint\Type;

interface OptionalKeyType extends Type {
	public Type $valueType { get; }
}