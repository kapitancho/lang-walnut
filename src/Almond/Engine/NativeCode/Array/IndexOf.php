<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, Value> */
final readonly class IndexOf extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, Type $parameterType): Type =>
			$this->typeRegistry->optional(
				$this->typeRegistry->integer(0,
					$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						max($targetType->range->maxLength - 1, 0)
				)
			);
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, Value $parameter): Value {
			foreach ($target->values as $index => $value) {
				if ($value->equals($parameter)) {
					return $this->valueRegistry->integer($index);
				}
			}
			return $this->valueRegistry->empty;
		};
	}

}
