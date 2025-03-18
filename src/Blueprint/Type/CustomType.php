<?php

namespace Walnut\Lang\Blueprint\Type;

interface CustomType extends NamedType {
	public Type $valueType { get; }
}