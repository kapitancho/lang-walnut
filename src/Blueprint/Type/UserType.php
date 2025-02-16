<?php

namespace Walnut\Lang\Blueprint\Type;

interface UserType extends NamedType {
	public Type $valueType { get; }
}