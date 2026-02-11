<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\False;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<FalseType, NullType, BooleanValue, NullValue> */
final readonly class UnaryBitwiseNot extends NativeMethod {

	protected function getValidator(): callable {
		return fn(FalseType $targetType, NullType $parameterType): TrueType =>
			$this->typeRegistry->true;
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(!$target->literalValue);
	}

}
