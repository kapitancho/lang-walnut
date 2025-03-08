<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\SubsetType;
use Walnut\Lang\Blueprint\Type\Type;

trait BaseTypeHelper {

	/**
	 * @param Type $sourceType
	 * @param Type $targetType
	 * @return Type|null
	 */
	public function toTargetBaseType(Type $sourceType, Type $targetType): Type|null {
		return match (true) {
			$sourceType instanceof AliasType =>
				$this->toTargetBaseType($sourceType->aliasedType, $targetType),
			$sourceType instanceof SubsetType =>
				$this->toTargetBaseType($sourceType->valueType, $targetType),
			$sourceType instanceof ProxyNamedType =>
				$this->toTargetBaseType($sourceType->actualType, $targetType),
			$sourceType instanceof IntersectionType =>
				$this->getIntersectionBaseType($sourceType, $targetType),
			default => $sourceType->isSubtypeOf($targetType) ? $sourceType : null
		};
	}

	private function getIntersectionBaseType(IntersectionType $sourceType, Type $targetType): Type|null {
		foreach ($sourceType->types as $type) {
			$baseType = $this->toTargetBaseType($type, $targetType);
			if ($baseType !== null) {
				return $baseType;
			}
		}
		return null;
	}

}