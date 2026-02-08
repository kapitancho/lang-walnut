<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Null;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<NullType, NullType, NullValue, NullValue> */
final readonly class AsInteger extends NativeMethod {

	protected function getValidator(): callable {
		return fn(NullType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->integerSubset([new Number(0)]);
	}

	protected function getExecutor(): callable {
		return fn(NullValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(0);
	}

}
