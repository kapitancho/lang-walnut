<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NumericRangeHelper;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class Square extends NativeMethod {
	use NumericRangeHelper;

	protected function getValidator(): callable {
		return fn(IntegerType $targetType, NullType $parameterType): IntegerType =>
			$this->typeRegistry->integerFull($this->getSquareRange($targetType));
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				$target->literalValue * $target->literalValue
			);
	}
}
