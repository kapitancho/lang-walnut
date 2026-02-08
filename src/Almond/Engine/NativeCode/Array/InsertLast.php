<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, Value> */
final readonly class InsertLast extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, Type $parameterType): ArrayType =>
			$this->typeRegistry->array(
				$this->typeRegistry->union([$targetType->itemType, $parameterType]),
				$targetType->range->minLength + 1,
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength + 1
			);
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, Value $parameter): TupleValue {
			$values = $target->values;
			$values[] = $parameter;
			return $this->valueRegistry->tuple($values);
		};
	}

}
