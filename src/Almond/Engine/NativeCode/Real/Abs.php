<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class Abs extends NativeMethod {

	protected function getValidator(): callable {
		return fn(IntegerType|RealType $targetType, NullType $parameterType): RealType =>
			$this->typeRegistry->realFull(
				new NumberInterval(
					match(true) {
						$targetType->numberRange->max !== PlusInfinity::value && $targetType->numberRange->max->value < 0 =>
							new NumberIntervalEndpoint(
								new Number((string)abs((float)(string)$targetType->numberRange->max->value)),
								$targetType->numberRange->max->inclusive
							),
						$targetType->numberRange->min !== MinusInfinity::value && $targetType->numberRange->min->value >= 0 =>
							$targetType->numberRange->min,
						default => new NumberIntervalEndpoint(new Number(0), true)
					},
					$targetType->numberRange->min === MinusInfinity::value || $targetType->numberRange->max === PlusInfinity::value ?
						PlusInfinity::value :
						new NumberIntervalEndpoint(
							new Number(
								(string)max(
									abs((float)(string)$targetType->numberRange->min->value),
									abs((float)(string)$targetType->numberRange->max->value)
								)
							),
							abs((float)(string)$targetType->numberRange->min->value) >
							abs((float)(string)$targetType->numberRange->max->value) ?
								$targetType->numberRange->min->inclusive :
								$targetType->numberRange->max->inclusive
						)
				)
			);
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, NullValue $parameter): RealValue =>
			$this->valueRegistry->real(abs((float)(string)$target->literalValue));
	}
}
