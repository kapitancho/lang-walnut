<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;

interface NamedType extends Type {
	public TypeName $name { get; }
}