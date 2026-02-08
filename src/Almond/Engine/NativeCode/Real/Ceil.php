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
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

final readonly class Ceil extends NativeMethod {

	protected function getValidator(): callable {
		return fn(RealType $targetType, NullType $parameterType, mixed $origin): IntegerType =>
			$this->typeRegistry->integerFull(... array_map(
				fn(NumberInterval $interval) => new NumberInterval(
					$interval->start === MinusInfinity::value ? MinusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->start->value->ceil(),
							(string)$interval->start->value !== (string)$interval->start->value->ceil() ||
							$interval->start->inclusive
						),
					$interval->end === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->end->value->ceil(),
							true
						)
				),
				$targetType->numberRange->intervals
			));
	}

	protected function getExecutor(): callable {
		return fn(RealValue|IntegerValue $target, NullValue $parameter) =>
			$this->valueRegistry->integer($target->literalValue->ceil());
	}
}
