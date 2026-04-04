<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, MapType, RecordValue> */
final readonly class Zip extends MapNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->map()) ?
			null : sprintf(
				"Parameter type %s is not a subtype of Map",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(MapType $targetType, Type $parameterType): Type {
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
					if ($tg instanceof OptionalType) {
						$tg = $tg->valueType;
						$isOptional = true;
					}
					if ($pr instanceof OptionalType) {
						$pr = $pr->valueType;
						$isOptional = true;
					}
					if (!$tg instanceof NothingType && !$pr instanceof NothingType) {
						$tuple = $this->typeRegistry->tuple([$tg, $pr], null);
						$resultType[$key] = $isOptional ? $this->typeRegistry->optional($tuple) : $tuple;
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
			/** @var MapType $pType */
			return $this->typeRegistry->map(
				$this->typeRegistry->tuple([
					$targetType->itemType,
					$pType->itemType,
				], null),
				min($targetType->range->minLength, $pType->range->minLength),
				match(true) {
					$targetType->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
					$pType->range->maxLength === PlusInfinity::value => $targetType->range->maxLength,
					default => min($targetType->range->maxLength, $pType->range->maxLength)
				},
				$this->typeRegistry->intersection([
					$targetType->keyType,
					$pType->keyType
				])
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
