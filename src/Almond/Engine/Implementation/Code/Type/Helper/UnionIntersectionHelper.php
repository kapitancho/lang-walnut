<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;

/** @var non-empty-list<Type> $types */
trait UnionIntersectionHelper {

	private function isRecordHelper(Type $ofType): bool {
		if (!$ofType instanceof RecordType) {
			return false;
		}
		$types = $this->types;
		if (array_any($types, fn($type) => !$type instanceof RecordType)) {
			return false;
		}
		/** @var \Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\RecordType[] $types */
		$allKeys = array_values(
			array_unique(
				array_merge(...
					array_map(static fn(RecordType $recordType): array =>
						array_keys($recordType->types), $types))));

		foreach ($allKeys as $key) {
			$propertyTypes = [];
			foreach($types as $type) {
				$propertyTypes[] = $type->types[$key] ?? $type->restType;
			}
			$propertyType = $this->normalizer->normalize(... $propertyTypes);
			if (!$propertyType->isSubtypeOf($ofType->types[$key] ?? $ofType->restType)) {
				return false;
			}
		}
		$restTypes = [];
		foreach($types as $type) {
			$restTypes[] = $type->restType;
		}
		$restType = $this->normalizer->normalize(... $restTypes);
		if (!$restType->isSubtypeOf($ofType->restType)) {
			return false;
		}
		return true;
	}

}