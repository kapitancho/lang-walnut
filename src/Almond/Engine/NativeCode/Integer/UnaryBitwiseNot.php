<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class UnaryBitwiseNot extends NativeMethod {

	private function bitwiseNot(int $x): int {
		return (~$x) & 0x7FFFFFFFFFFFFFFF;
	}

	protected function getValidator(): callable {
		return function(IntegerType $targetType, NullType $parameterType, mixed $origin): IntegerType|ValidationFailure {
			if (
				$targetType->numberRange->min instanceof NumberIntervalEndpoint &&
				$targetType->numberRange->min->value >= 0 &&
				$targetType->numberRange->min->value <= PHP_INT_MAX
			) {
				return $this->typeRegistry->integer(
					$this->bitwiseNot((int)(string)$targetType->numberRange->max->value),
					$this->bitwiseNot((int)(string)$targetType->numberRange->min->value)
				);
			}
			return $this->validationFactory->error(
				\Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				origin: $origin
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				$this->bitwiseNot((int)(string)$target->literalValue)
			);
	}
}
