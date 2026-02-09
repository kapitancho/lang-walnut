<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class RoundAsInteger extends NativeMethod {

	protected function getValidator(): callable {
		return fn(IntegerType|RealType $targetType, NullType $parameterType): IntegerType =>
			$this->typeRegistry->integer(
				$targetType->numberRange->min === MinusInfinity::value ? MinusInfinity::value :
					$targetType->numberRange->min->value->round(),
				$targetType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					$targetType->numberRange->max->value->round()
			);
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer($target->literalValue->round());
	}
}
