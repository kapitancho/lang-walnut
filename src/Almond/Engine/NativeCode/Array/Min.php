<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, NullType, NullValue> */
final readonly class Min extends ArrayNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return $targetItemType->isSubtypeOf(
			$this->typeRegistry->union([$this->typeRegistry->integer(), $this->typeRegistry->real()])
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, NullType $parameterType, mixed $origin): Type|ValidationFailure {
			if ($targetType->range->minLength > 0) {
				return $targetType->itemType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				origin: $origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): Value {
			$bestV = $target->values[0];
			$best = $bestV->literalValue;
			foreach ($target->values as $item) {
				$value = $item->literalValue;
				if ($value < $best) {
					$bestV = $item;
					$best = $value;
				}
			}
			return $bestV;
		};
	}

}
