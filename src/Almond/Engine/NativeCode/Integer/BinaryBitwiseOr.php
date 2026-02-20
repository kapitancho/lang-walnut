<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, IntegerType, IntegerValue, IntegerValue> */
final readonly class BinaryBitwiseOr extends NativeMethod {


	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		return $targetType instanceof IntegerType &&
		$targetType->numberRange->min instanceof NumberIntervalEndpoint &&
		$targetType->numberRange->min->value >= 0 &&
		$targetType->numberRange->min->value <= PHP_INT_MAX ?
			null :
			"Target type of binary bitwise and must be an integer with a minimum value between 0 and " . PHP_INT_MAX . ".";
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var IntegerType $parameterType */
		return $parameterType->numberRange->min instanceof NumberIntervalEndpoint &&
		$parameterType->numberRange->min->value >= 0 &&
		$parameterType->numberRange->min->value <= PHP_INT_MAX ?
			null :
			"Parameter type of binary bitwise and must be an integer with a minimum value between 0 and " . PHP_INT_MAX . ".";
	}

	protected function getValidator(): callable {
		return function(IntegerType $targetType, IntegerType $parameterType): IntegerType {
			$min = max($targetType->numberRange->min->value, $parameterType->numberRange->min->value);
			$max = 2 * max($targetType->numberRange->max->value, $parameterType->numberRange->max->value);
			return $this->typeRegistry->integer($min, $max);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, IntegerValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				(int)(string)$target->literalValue | (int)(string)$parameter->literalValue
			);
	}
}
