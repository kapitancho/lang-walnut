<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, IntegerType, IntegerValue, IntegerValue> */
final readonly class BinaryIntegerDivide extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType $targetType, IntegerType $parameterType): IntegerType|ResultType {
			if ((string)$parameterType->numberRange === '1') {
				return $targetType;
			}

			$int = $this->typeRegistry->integer();
			if (
				$targetType->numberRange->min instanceof NumberIntervalEndpoint && $targetType->numberRange->min->value >= 0 &&
				$parameterType->numberRange->min instanceof NumberIntervalEndpoint && $parameterType->numberRange->min->value >= 0
			) {
				$pMin = match(true) {
					$parameterType->numberRange->min === MinusInfinity::value => MinusInfinity::value,
					(string)$parameterType->numberRange->min->value === '0' => new Number(1),
					default => $parameterType->numberRange->min->value
				};
				$pMax = match(true) {
					$parameterType->numberRange->max === PlusInfinity::value => PlusInfinity::value,
					(string)$parameterType->numberRange->max->value === '0' => new Number(-1),
					default => $parameterType->numberRange->max->value
				};
				$min = $parameterType->numberRange->max === PlusInfinity::value ?
					new NumberIntervalEndpoint(
						new Number(0),
						true
					) :
					new NumberIntervalEndpoint(
						$targetType->numberRange->min->value->div($pMax)->floor(),
						true
					);
				$max = $targetType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					new NumberIntervalEndpoint(
						$targetType->numberRange->max->value->div($pMin)->floor(),
						true
					);
				$interval = new NumberInterval($min, $max);
				$int = $this->typeRegistry->integerFull($interval);
			}

			return $parameterType->contains(0) ?
				$this->typeRegistry->result(
					$int,
					$this->typeRegistry->core->notANumber
				) : $int;
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue $target, IntegerValue $parameter): IntegerValue|ErrorValue {
			if ((int)(string)$parameter->literalValue === 0) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->notANumber
				);
			}
			return $this->valueRegistry->integer(
				intdiv((int)(string)$target->literalValue, (int)(string)$parameter->literalValue)
			);
		};
	}

}
