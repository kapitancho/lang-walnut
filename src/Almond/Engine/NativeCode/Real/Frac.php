<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class Frac extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, NullType $parameterType): Type {
			if ($targetType instanceof IntegerType) {
				return $this->typeRegistry->integerSubset([new Number(0)]);
			}
			if ($targetType instanceof RealSubsetType) {
				return $this->typeRegistry->realSubset(
					array_values(
						array_unique(
							array_map(
								fn(Number $v) => $this->calculateFrac($v),
								$targetType->subsetValues
							)
						)
					)
				);
			}
			$min = $targetType->numberRange->min;
			$max = $targetType->numberRange->max;

			return $this->typeRegistry->realFull(
				new NumberInterval(
					match(true) {
						$min === MinusInfinity::value || $min->value <= -1 => new NumberIntervalEndpoint(new Number(-1), false),
						$min->value < 1 => $min,
						default => new NumberIntervalEndpoint(new Number(0), true),
					},
					match(true) {
						$max === PlusInfinity::value || $max->value >= 1 => new NumberIntervalEndpoint(new Number(1), false),
						$max->value > -1 => $max,
						default => new NumberIntervalEndpoint(new Number(0), true),
					}
				),
			);
		};
	}

	private function calculateFrac(Number $value): Number {
		if ($value > 0) {
			return $value->sub($value->floor());
		} elseif ($value < 0) {
			return $value->sub($value->ceil());
		} else {
			return new Number(0);
		}
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, NullValue $parameter): RealValue =>
			$this->valueRegistry->real($this->calculateFrac($target->literalValue));
	}
}
