<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\NumericBinaryDivide;

/** @extends NumericBinaryDivide<RealType, RealValue> */
final readonly class BinaryDivide extends NumericBinaryDivide {

	protected function getValidator(): callable {
		return fn(
			RealType $targetType,
			RealType $parameterType,
			mixed $origin
		): RealType|ResultType =>
			$this->doValidate($targetType, $parameterType);
	}

	protected function getExecutor(): callable {
		return fn(
			RealValue $target,
			RealValue $parameter
		): RealValue|ErrorValue =>
			$this->doDivide($target, $parameter);
	}

}
