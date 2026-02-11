<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<MapType, TypeType, TypeValue> */
final readonly class WithKeyType extends TypeNativeMethod {

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType, mixed $origin): TypeType|ValidationFailure {
			if (!$parameterType->isSubtypeOf(
				$this->typeRegistry->type($this->typeRegistry->string())
			)) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					$origin
				);
			}
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof MapType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->map(
						$refType->itemType,
						$refType->range->minLength,
						$refType->range->maxLength,
						$parameterType->refType,
					)
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var MapType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			$result = $this->typeRegistry->map(
				$typeValue->itemType,
				$typeValue->range->minLength,
				$typeValue->range->maxLength,
				$parameter->typeValue,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
