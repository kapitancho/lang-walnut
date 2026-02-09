<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, IntegerType, IntegerValue, IntegerValue> */
final readonly class BinaryBitwiseOr extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool {
		return $targetType instanceof IntegerType &&
			$targetType->numberRange->min instanceof NumberIntervalEndpoint &&
			$targetType->numberRange->min->value >= 0 &&
			$targetType->numberRange->min->value <= PHP_INT_MAX;
	}

	protected function getValidator(): callable {
		return function(IntegerType $targetType, IntegerType $parameterType, mixed $origin): IntegerType|ValidationFailure {
			if (
				$parameterType->numberRange->min instanceof NumberIntervalEndpoint &&
				$parameterType->numberRange->min->value >= 0 &&
				$parameterType->numberRange->min->value <= PHP_INT_MAX
			) {
				$min = max($targetType->numberRange->min->value, $parameterType->numberRange->min->value);
				$max = 2 * max($targetType->numberRange->max->value, $parameterType->numberRange->max->value);
				return $this->typeRegistry->integer($min, $max);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, IntegerValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				(int)(string)$target->literalValue | (int)(string)$parameter->literalValue
			);
	}
}
