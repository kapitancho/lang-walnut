<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias\MapValues;

final readonly class Values extends MapValues {

	public function validateX(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		$checkTypes = $targetType instanceof IntersectionType
			? $targetType->types
			: [$targetType];
		foreach($checkTypes as $checkType) {
			$checkType = $this->toBaseType($checkType);
			if ($checkType instanceof RecordType) {
				$checkType = $checkType->asMapType();
			}
			if ($checkType instanceof MapType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->array(
						$checkType->itemType, $checkType->range->minLength, $checkType->range->maxLength
					)
				);
			}
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

}
