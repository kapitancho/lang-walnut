<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface NamedType extends Type {
	public TypeName $name { get; }
}