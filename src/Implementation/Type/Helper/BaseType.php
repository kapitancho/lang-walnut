<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\Value;

trait BaseType {
	public function toBaseType(Type $targetType): Type {
		while ($targetType instanceof AliasType || $targetType instanceof SubtypeType || $targetType instanceof ProxyNamedType) {
			if ($targetType instanceof AliasType) {
				$targetType = $targetType->aliasedType;
			}
			if ($targetType instanceof SubtypeType) {
				$targetType = $targetType->baseType;
			}
			if ($targetType instanceof ProxyNamedType) {
				$targetType = $targetType->actualType;
			}
		}
		return $targetType;
	}

	public function toBaseValue(Value $targetValue): Value {
		while ($targetValue instanceof SubtypeValue) {
			$targetValue = $targetValue->baseValue;
		}
		return $targetValue;
	}
}