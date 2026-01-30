<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;

interface TypeFinder {
	public function typeByName(TypeName $typeName): Type|UnknownType;
}