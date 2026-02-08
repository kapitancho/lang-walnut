<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\False;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<FalseType, NullType, BooleanValue, NullValue> */
final readonly class AsInteger extends NativeMethod {

	protected function getValidator(): callable {
		return fn(FalseType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->integerSubset([new Number(0)]);
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(0);
	}

}
