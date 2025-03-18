<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\Type;

trait BaseType {
	public function toBaseType(Type $targetType, $ignoreSubsetType = false): Type {
		$step = true;
		while ($step) {
			$step = false;
			if ($targetType instanceof AliasType) {
				$step = true;
				$targetType = $targetType->aliasedType;
			}
			if (!$ignoreSubsetType && $targetType instanceof SubsetType) {
				$step = true;
				$targetType = $targetType->valueType;
			}
			if ($targetType instanceof ProxyNamedType) {
				$step = true;
				$targetType = $targetType->actualType;
			}
		}
		return $targetType;
	}
}