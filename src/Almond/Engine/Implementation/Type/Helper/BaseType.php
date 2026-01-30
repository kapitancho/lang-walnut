<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\Helper;

use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

trait BaseType {
	public function toBaseType(Type $targetType): Type {
		while ($targetType instanceof AliasType) {
			$targetType = $targetType->aliasedType;
		}
		return $targetType;
	}
}