<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, ArrayType|TupleType, TupleValue, TupleValue> */
final readonly class Zip extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
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
			if ($pType instanceof ArrayType) {
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
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
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
