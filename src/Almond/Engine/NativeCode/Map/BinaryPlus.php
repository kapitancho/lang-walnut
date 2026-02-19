<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, MapType|RecordType, RecordValue> */
final readonly class BinaryPlus extends MapNativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		$parameterType = $this->toBaseType($parameterType);
		return $parameterType instanceof MapType || $parameterType instanceof RecordType;
	}

	protected function getValidator(): callable {
		return function(MapType $targetType, Type $parameterType): Type {
			$parameterType = $this->toBaseType($parameterType);
			$parameterType = $parameterType instanceof RecordType ? $parameterType->asMapType() : $parameterType;
			/** @var MapType $parameterType */
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
