<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface NativeCodeTypeMapper {
	/** @return array<TypeName> */
	public function getTypesFor(Type $type): array;
}