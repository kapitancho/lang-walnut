<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
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
			// @codeCoverageIgnoreStart
			$sourceType instanceof ProxyNamedType =>
				$this->toTargetBaseType($sourceType->actualType, $targetType),
			// @codeCoverageIgnoreEnd
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
		// @codeCoverageIgnoreStart
		return null;
		// @codeCoverageIgnoreEnd
	}

}