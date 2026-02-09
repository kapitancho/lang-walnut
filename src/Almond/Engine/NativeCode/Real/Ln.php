<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class Ln extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, NullType $parameterType): Type {
			$min = $targetType->numberRange->min;
			$max = $targetType->numberRange->max;
			$real = $this->typeRegistry->real(max: $max === PlusInfinity::value ? PlusInfinity::value : $max->value);
			return $min instanceof NumberIntervalEndpoint && ($min->value > 0 || ($min->value == 0 && !$min->inclusive)) ?
				$real :
				$this->typeRegistry->result(
					$real,
					$this->typeRegistry->core->notANumber
				);
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue|RealValue $target, NullValue $parameter): Value {
			$val = (string)$target->literalValue;
			return $val > 0 ? $this->valueRegistry->real(
				log((float)$val)
			) : $this->valueRegistry->error(
				$this->valueRegistry->core->notANumber
			);
		};
	}
}
