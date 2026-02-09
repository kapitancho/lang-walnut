<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class Abs extends NativeMethod {

	protected function getValidator(): callable {
		return fn(IntegerType $targetType, NullType $parameterType): IntegerType =>
			$this->typeRegistry->integerFull(
				new NumberInterval(
					match(true) {
						$targetType->numberRange->max !== PlusInfinity::value && $targetType->numberRange->max->value < 0 =>
							new NumberIntervalEndpoint(
								new Number(abs((int)(string)$targetType->numberRange->max->value)),
								$targetType->numberRange->max->inclusive
							),
						$targetType->numberRange->min !== MinusInfinity::value && $targetType->numberRange->min->value >= 0 =>
							$targetType->numberRange->min,
						default => new NumberIntervalEndpoint(new Number(0), true)
					},
					$targetType->numberRange->min === MinusInfinity::value ||
						$targetType->numberRange->max === PlusInfinity::value ?
							PlusInfinity::value :
							new NumberIntervalEndpoint(
								new Number(
									max(
										abs((int)(string)$targetType->numberRange->min->value),
										abs((int)(string)$targetType->numberRange->max->value)
									)
								),
								abs((int)(string)$targetType->numberRange->min->value) >
								abs((int)(string)$targetType->numberRange->max->value) ?
									$targetType->numberRange->min->inclusive :
									$targetType->numberRange->max->inclusive
							)
				)
			);
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(abs((int)(string)$target->literalValue));
	}
}
