<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use BcMath\Number;
use RoundingMode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
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
final readonly class AsInteger extends NativeMethod {

	protected function getValidator(): callable {
		return function(RealType|IntegerType $targetType, NullType $parameterType, mixed $origin): IntegerType {
			if ($targetType instanceof IntegerSubsetType || $targetType instanceof RealSubsetType) {
				return $this->typeRegistry->integerSubset(
					array_map(fn(Number $v) => $v > 0 ? $v->floor() : $v->ceil(),
						$targetType->subsetValues)
				);
			}
			return $this->typeRegistry->integerFull(... array_map(
				fn(NumberInterval $interval) => new NumberInterval(
					$interval->start === MinusInfinity::value ? MinusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->start->value->round(0, RoundingMode::TowardsZero),
							(string)$interval->start->value !== (string)$interval->start->value->round(0, RoundingMode::TowardsZero) ||
							$interval->start->inclusive
						),
					$interval->end === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->end->value->round(0, RoundingMode::TowardsZero),
							(string)$interval->end->value !== (string)$interval->end->value->round(0, RoundingMode::TowardsZero) ||
							$interval->end->inclusive
						)
				),
				$targetType->numberRange->intervals
			));
		};
	}

	protected function getExecutor(): callable {
		return fn(RealValue|IntegerValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				$target->literalValue->round(0, RoundingMode::TowardsZero)
			);
	}

}