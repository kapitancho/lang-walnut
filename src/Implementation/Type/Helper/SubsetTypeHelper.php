<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\ProxyNamedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type;

trait SubsetTypeHelper {
	public function stringSubsetDiff(TypeRegistry $typeRegistry, StringSubsetType $target, StringSubsetType $excluding): StringSubsetType|NothingType {
		$excludedValues = array_filter(
			$target->subsetValues,
			fn(string $value): bool => !$excluding->contains($value)
		);
		if ($excludedValues === []) {
			return $typeRegistry->nothing;
		}
		return $typeRegistry->stringSubset($excludedValues);
	}

}