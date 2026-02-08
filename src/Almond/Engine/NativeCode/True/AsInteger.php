<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\True;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<TrueType, NullType, BooleanValue, NullValue> */
final readonly class AsInteger extends NativeMethod {

	protected function getValidator(): callable {
		return fn(TrueType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->integerSubset([new Number(1)]);
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(1);
	}

}
