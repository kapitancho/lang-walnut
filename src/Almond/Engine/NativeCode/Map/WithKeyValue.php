<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MapType|RecordType, RecordType, RecordValue, RecordValue> */
final readonly class WithKeyValue extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof RecordType || $targetType instanceof MapType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->record([
					'key' => $this->typeRegistry->string(),
					'value' => $this->typeRegistry->any
				], null)
			)) {
				$pType = $this->toBaseType($parameterType);
				$keyType = $pType instanceof RecordType ? ($pType->types['key'] ?? null) : null;
				if ($targetType instanceof RecordType) {
					if ($keyType instanceof StringSubsetType && count($keyType->subsetValues) === 1) {
						$keyValue = $keyType->subsetValues[0];
						$valueType = $pType instanceof RecordType ? ($pType->types['value'] ?? null) : null;
						return $this->typeRegistry->record(
							$targetType->types + [
								$keyValue => $valueType
							],
							$targetType->restType
						);
					}
					$targetType = $targetType->asMapType();
				}
				$valueType = $pType instanceof RecordType ? ($pType->types['value'] ?? null) : null;
				return $this->typeRegistry->map(
					$this->typeRegistry->union(array_filter([
						$targetType->itemType,
						$valueType
					])),
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength + 1,
					$this->typeRegistry->union(array_filter([
						$targetType->keyType,
						$keyType
					]))
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
		return function(RecordValue $target, RecordValue $parameter): RecordValue {
			$p = $parameter->values;
			/** @var StringValue $pKey */
			$pKey = $p['key'];
			/** @var Value $pValue */
			$pValue = $p['value'];
			$values = $target->values;
			$values[$pKey->literalValue] = $pValue;
			return $this->valueRegistry->record($values);
		};
	}

}
