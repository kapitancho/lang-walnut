<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class UnaryPlus extends NativeMethod {

	protected function getValidator(): callable {
		return fn(IntegerType $targetType, NullType $parameterType): IntegerType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer($target->literalValue);
	}
}
