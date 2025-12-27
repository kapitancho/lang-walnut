<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait SetIsSubsetOfIsSupersetOf {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof SetType) {
			if ($parameterType instanceof SetType) {
				return $typeRegistry->boolean;
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function isSubset(SetValue $value, SetValue $ofValue, bool $strict = false): bool {
		$isSubset = !array_any(
			array_keys($value->valueSet),
			fn($key) => !array_key_exists($key, $ofValue->valueSet)
		);
		return $isSubset && (!$strict || count($value->values) < count($ofValue->values));
	}

}