<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, Value> */
final readonly class WithoutAll extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, Type $parameterType): ArrayType =>
			$this->typeRegistry->array(
				$targetType->itemType,
				0,
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, Value $parameter): TupleValue {
			$result = [];
			foreach ($target->values as $value) {
				if (!$value->equals($parameter)) {
					$result[] = $value;
				}
			}
			return $this->valueRegistry->tuple($result);
		};
	}

}
