<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, MapType|RecordType, RecordValue> */
final readonly class MergeWith extends MapNativeMethod {

	protected function getValidator(): callable {
		return function(MapType $targetType, MapType|RecordType $parameterType): MapType {
			$parameterType = $parameterType instanceof RecordType ? $parameterType->asMapType() : $parameterType;
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
		};
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, RecordValue $parameter): RecordValue =>
			$this->valueRegistry->record([... $target->values, ... $parameter->values]);
	}

}
