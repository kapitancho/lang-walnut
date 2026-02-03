<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;

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