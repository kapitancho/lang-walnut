<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, IntegerType, IntegerValue, IntegerValue> */
final readonly class BinaryBitwiseAnd extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator): bool {
		return $targetType instanceof IntegerType &&
			$targetType->numberRange->min instanceof NumberIntervalEndpoint &&
			$targetType->numberRange->min->value >= 0 &&
			$targetType->numberRange->min->value <= PHP_INT_MAX;
	}

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		/** @var IntegerType $parameterType */
		return $parameterType->numberRange->min instanceof NumberIntervalEndpoint &&
			$parameterType->numberRange->min->value >= 0 &&
			$parameterType->numberRange->min->value <= PHP_INT_MAX;
	}

	protected function getValidator(): callable {
		return function(IntegerType $targetType, IntegerType $parameterType): IntegerType {
			$min = 0;
			$max = min($parameterType->numberRange->min->value, $parameterType->numberRange->max->value);
			return $this->typeRegistry->integer($min, $max);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, IntegerValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				(int)(string)$target->literalValue & (int)(string)$parameter->literalValue
			);
	}
}
