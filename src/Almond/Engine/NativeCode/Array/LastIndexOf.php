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
final readonly class LastIndexOf extends ArrayNativeMethod {

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
			$values = $target->values;
			for ($i = count($values) - 1; $i >= 0; $i--) {
				if ($values[$i]->equals($parameter)) {
					return $this->valueRegistry->integer($i);
				}
			}
			return $this->valueRegistry->empty;
		};
	}

}
