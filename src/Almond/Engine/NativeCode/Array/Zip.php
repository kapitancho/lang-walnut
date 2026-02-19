<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, ArrayType|TupleType, TupleValue> */
final readonly class Zip extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->array()) ?
			null : sprintf(
				"Parameter type %s is not a subtype of Array",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType): Type {
			$pType = $this->toBaseType($parameterType);
			if ($targetType instanceof TupleType && $pType instanceof TupleType) {
				$resultType = [];
				$maxLength = max(count($targetType->types), count($pType->types));
				for ($i = 0; $i < $maxLength; $i++) {
					$tg = $targetType->types[$i] ?? $targetType->restType;
					$pr = $pType->types[$i] ?? $pType->restType;
					if (!$tg instanceof NothingType && !$pr instanceof NothingType) {
						$resultType[] = $this->typeRegistry->tuple([$tg, $pr], null);
					} else {
						break;
					}
				}
				return $this->typeRegistry->tuple($resultType,
					$targetType->restType instanceof NothingType || $pType->restType instanceof NothingType ?
						$this->typeRegistry->nothing : $this->typeRegistry->tuple([
							$targetType->restType,
							$pType->restType,
					], null)
				);
			}
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$pType = $pType instanceof TupleType ? $pType->asArrayType() : $pType;
			/** @var ArrayType $pType */
			return $this->typeRegistry->array(
				$this->typeRegistry->tuple([
					$type->itemType,
					$pType->itemType,
				], null),
				min($type->range->minLength, $pType->range->minLength),
				match(true) {
					$type->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
					$pType->range->maxLength === PlusInfinity::value => $type->range->maxLength,
					default => min($type->range->maxLength, $pType->range->maxLength)
				}
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, TupleValue $parameter): TupleValue {
			$values = $target->values;
			$pValues = $parameter->values;
			$result = [];
			foreach ($values as $index => $value) {
				$pValue = $pValues[$index] ?? null;
				if (!$pValue) {
					break;
				}
				$result[] = $this->valueRegistry->tuple([$value, $pValue]);
			}
			return $this->valueRegistry->tuple($result);
		};
	}

}
