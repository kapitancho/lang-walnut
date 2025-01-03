<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface NamedType extends Type {
	public TypeNameIdentifier $name { get; }
}