<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

trait SetIsSubsetOfIsSupersetOf {
	use BaseType;

	private function validateSetComparison(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof SetType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof SetType) {
				return $this->validationFactory->validationSuccess($this->typeRegistry->boolean);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
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
