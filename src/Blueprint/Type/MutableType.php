<?php

namespace Walnut\Lang\Blueprint\Type;

interface MutableType extends Type {
	public Type $valueType { get; }
}