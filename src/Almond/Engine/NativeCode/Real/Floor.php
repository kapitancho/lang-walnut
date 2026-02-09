<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval as NumberIntervalInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class Floor extends NativeMethod {

	protected function getValidator(): callable {
		return fn(IntegerType|RealType $targetType, NullType $parameterType): IntegerType =>
			$this->typeRegistry->integerFull(... array_map(
				fn(NumberIntervalInterface $interval) => new NumberInterval(
					$interval->start === MinusInfinity::value ? MinusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->start->value->floor(),
							true
						),
					$interval->end === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->end->value->floor(),
							(string)$interval->end->value !== (string)$interval->end->value->floor() ||
							$interval->end->inclusive
						)
				),
				$targetType->numberRange->intervals
			));
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer($target->literalValue->floor());
	}
}
