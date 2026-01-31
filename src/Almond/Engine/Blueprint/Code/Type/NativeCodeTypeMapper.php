<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

interface NativeCodeTypeMapper {
	/** @return array<TypeName> */
	public function getTypesFor(Type $type): array;
}