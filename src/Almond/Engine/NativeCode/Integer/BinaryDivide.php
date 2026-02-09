<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\NumericBinaryDivide;

/** @extends NumericBinaryDivide<IntegerType, IntegerValue> */
final readonly class BinaryDivide extends NumericBinaryDivide {


	protected function getValidator(): callable {
		return fn(
			IntegerType $targetType,
			IntegerType|RealType $parameterType,
			mixed $origin
		): IntegerType|RealType|ResultType =>
		$this->doValidate($targetType, $parameterType);
	}

	protected function getExecutor(): callable {
		return fn(
			IntegerValue $target,
			IntegerValue|RealValue $parameter
		): IntegerValue|RealValue|ErrorValue =>
		$this->doDivide($target, $parameter);
	}

}
