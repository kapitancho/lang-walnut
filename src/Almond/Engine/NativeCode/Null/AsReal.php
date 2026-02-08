<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Null;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<NullType, NullType, NullValue, NullValue> */
final readonly class AsReal extends NativeMethod {

	protected function getValidator(): callable {
		return fn(NullType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->realSubset([new Number(0)]);
	}

	protected function getExecutor(): callable {
		return fn(NullValue $target, NullValue $parameter): RealValue =>
			$this->valueRegistry->real(0);
	}

}
