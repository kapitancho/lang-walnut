<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

trait BaseType {
	public function toBaseType(Type $targetType): Type {
		while ($targetType instanceof AliasType) {
			$targetType = $targetType->aliasedType;
		}
		return $targetType;
	}
}