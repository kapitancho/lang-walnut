<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MapType|RecordType, MapType|RecordType, RecordValue, RecordValue> */
final readonly class Zip extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof MapType || $targetType instanceof RecordType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$pType = $this->toBaseType($parameterType);
			if ($targetType instanceof RecordType && $pType instanceof RecordType) {
				$resultType = [];
				$keys = array_values(array_unique(array_merge(
					array_keys($targetType->types), array_keys($pType->types)
				)));
				foreach ($keys as $key) {
					$tg = $targetType->types[$key] ?? $targetType->restType;
					$pr = $pType->types[$key] ?? $pType->restType;
					$isOptional = false;
					if ($tg instanceof OptionalKeyType) {
						$tg = $tg->valueType;
						$isOptional = true;
					}
					if ($pr instanceof OptionalKeyType) {
						$pr = $pr->valueType;
						$isOptional = true;
					}
					if (!$tg instanceof NothingType && !$pr instanceof NothingType) {
						$tuple = $this->typeRegistry->tuple([$tg, $pr], null);
						$resultType[$key] = $isOptional ? $this->typeRegistry->optionalKey($tuple) : $tuple;
					}
				}
				return $this->typeRegistry->record($resultType,
					$targetType->restType instanceof NothingType || $pType->restType instanceof NothingType ?
						$this->typeRegistry->nothing : $this->typeRegistry->tuple([
						$targetType->restType,
						$pType->restType,
					], null)
				);
			}
			$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
			$pType = $pType instanceof RecordType ? $pType->asMapType() : $pType;
			if ($pType instanceof MapType) {
				return $this->typeRegistry->map(
					$this->typeRegistry->tuple([
						$type->itemType,
						$pType->itemType,
					], null),
					min($type->range->minLength, $pType->range->minLength),
					match(true) {
						$type->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
						$pType->range->maxLength === PlusInfinity::value => $type->range->maxLength,
						default => min($type->range->maxLength, $pType->range->maxLength)
					},
					$this->typeRegistry->intersection([
						$type->keyType,
						$pType->keyType
					])
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
			$values = $target->values;
			$pValues = $parameter->values;
			$result = [];
			foreach($values as $key => $value) {
				$pValue = $pValues[$key] ?? null;
				if (!$pValue) {
					continue;
				}
				$result[$key] = $this->valueRegistry->tuple([$value, $pValue]);
			}
			return $this->valueRegistry->record($result);
		};
	}

}
