<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval as NumberIntervalInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class UnaryMinus extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType $targetType, NullType $parameterType): IntegerType {
			if ($targetType instanceof IntegerSubsetType) {
				return $this->typeRegistry->integerSubset(
					array_map(fn(Number $v): Number =>
						$v->mul(-1),
						$targetType->subsetValues
					)
				);
			}
			return $this->typeRegistry->integerFull(...
				array_map(
					fn(NumberIntervalInterface $interval): NumberIntervalInterface =>
						new NumberInterval(
							$interval->end instanceof PlusInfinity ?
								MinusInfinity::value :
								new NumberIntervalEndpoint(
									$interval->end->value->mul(-1),
									$interval->end->inclusive
								),
							$interval->start instanceof MinusInfinity ?
								PlusInfinity::value :
								new NumberIntervalEndpoint(
									$interval->start->value->mul(-1),
									$interval->start->inclusive
								)
						),
					$targetType->numberRange->intervals
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				new Number(0)->sub($target->literalValue)
			);
	}
}
