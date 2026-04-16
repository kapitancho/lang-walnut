<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, RecordType, RecordValue> */
final readonly class WithKeyValue extends MapNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->record([
				'key' => $this->typeRegistry->string(),
				'value' => $this->typeRegistry->any,
			], null)
		) ? null : sprintf(
			"Parameter type %s is not compatible with expected type [key: String, Value: Any].",
			$parameterType
		);
	}

	protected function getValidator(): callable {
		return function(MapType $targetType, RecordType $parameterType): Type {
			/** @var Type $keyType */
			$keyType = $parameterType->typeOf('key');
			/** @var Type $valueType */
			$valueType = $parameterType->typeOf('value');
			if ($targetType instanceof RecordType) {
				if ($keyType instanceof StringSubsetType && count($keyType->subsetValues) === 1) {
					$keyValue = $keyType->subsetValues[0];
					return $this->typeRegistry->record(
						[
							$keyValue => $valueType
						] + $targetType->types,
						$targetType->restType
					);
				}
			}
			return $this->typeRegistry->map(
				$this->typeRegistry->union([
					$targetType->itemType,
					$valueType
				]),
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength + 1,
				$this->typeRegistry->union([
					$targetType->keyType,
					$keyType
				])
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, RecordValue $parameter): RecordValue {
			$p = $parameter->values;
			/** @var StringValue $pKey */
			$pKey = $p['key'];
			/** @var Value $pValue */
			$pValue = $p['value'] ?? $this->valueRegistry->empty;
			$values = $target->values;
			if ($pValue instanceof EmptyValue) {
				unset($values[$pKey->literalValue]);
			} else {
				$values[$pKey->literalValue] = $pValue;
			}
			return $this->valueRegistry->record($values);
		};
	}

}
