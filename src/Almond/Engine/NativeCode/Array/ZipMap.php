<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, TupleValue> */
final readonly class ZipMap extends ArrayNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return $targetItemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$itemType = $targetType->itemType;
			$parameterType = $this->toBaseType($parameterType);
			$pType = $parameterType instanceof TupleType ? $parameterType->asArrayType() : $parameterType;
			if ($pType instanceof ArrayType) {
				return $this->typeRegistry->map(
					$pType->itemType,
					min(1, $targetType->range->minLength, $pType->range->minLength),
					match(true) {
						$targetType->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
						$pType->range->maxLength === PlusInfinity::value => $targetType->range->maxLength,
						default => min($targetType->range->maxLength, $pType->range->maxLength)
					},
					$itemType
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
		return function(TupleValue $target, TupleValue $parameter): Value {
			$values = $target->values;
			$pValues = $parameter->values;
			$result = [];
			foreach ($values as $value) {
				/** @var StringValue $value */
				if (count($pValues) === 0) {
					break;
				}
				$pValue = array_shift($pValues);
				$result[$value->literalValue] = $pValue;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
