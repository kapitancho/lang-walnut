<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, Value> */
final readonly class Without extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, Type $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->array(
					$targetType->itemType,
					max(0, $targetType->range->minLength - 1),
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : max($targetType->range->maxLength - 1, 0)
				),
				$this->typeRegistry->core->itemNotFound
			);
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, Value $parameter): TupleValue|ErrorValue {
			$values = $target->values;
			foreach ($values as $index => $value) {
				if ($value->equals($parameter)) {
					array_splice($values, $index, 1);
					return $this->valueRegistry->tuple($values);
				}
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
