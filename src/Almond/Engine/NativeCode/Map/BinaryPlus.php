<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, MapType|RecordType, RecordValue> */
final readonly class BinaryPlus extends MapNativeMethod {

	protected function getValidator(): callable {
		return function(MapType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$parameterType = $this->toBaseType($parameterType);
			$parameterType = $parameterType instanceof RecordType ? $parameterType->asMapType() : $parameterType;
			if ($parameterType instanceof MapType) {
				return $this->typeRegistry->map(
					$this->typeRegistry->union([
						$targetType->itemType,
						$parameterType->itemType
					]),
					max($targetType->range->minLength, $parameterType->range->minLength),
					$targetType->range->maxLength === PlusInfinity::value ||
						$parameterType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength + $parameterType->range->maxLength,
					$this->typeRegistry->union([
						$targetType->keyType,
						$parameterType->keyType
					]),
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, RecordValue $parameter): RecordValue =>
			$this->valueRegistry->record([... $target->values, ... $parameter->values]);
	}

}
