<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NumericRangeHelper;

/** @extends NativeMethod<IntegerType, IntegerType, IntegerValue, IntegerValue> */
final readonly class DownTo extends NativeMethod {
	use NumericRangeHelper;

	protected function getValidator(): callable {
		return fn(IntegerType $targetType, IntegerType $parameterType): ArrayType =>
			$this->getFromToAsArray($targetType->numberRange, $parameterType->numberRange);
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, IntegerValue $parameter): TupleValue =>
			$this->valueRegistry->tuple(
				$target->literalValue > $parameter->literalValue ?
					array_map(fn(int $i): IntegerValue =>
						$this->valueRegistry->integer($i),
						range($target->literalValue, $parameter->literalValue, -1)
					) : []
			);
	}
}
