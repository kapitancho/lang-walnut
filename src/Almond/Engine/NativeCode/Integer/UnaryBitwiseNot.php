<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class UnaryBitwiseNot extends NativeMethod {

	private function bitwiseNot(int $x): int {
		return (~$x) & 0x7FFFFFFFFFFFFFFF;
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator): bool {
		if (!parent::isTargetTypeValid($targetType, $validator)) {
			return false;
		}
		/** @var IntegerType $targetType */
		return $targetType->numberRange->min instanceof NumberIntervalEndpoint &&
			$targetType->numberRange->min->value >= 0 &&
			$targetType->numberRange->min->value <= PHP_INT_MAX;
	}

	protected function getValidator(): callable {
		return function(IntegerType $targetType, NullType $parameterType): IntegerType {
			return $this->typeRegistry->integer(
				$this->bitwiseNot((int)(string)$targetType->numberRange->max->value),
				$this->bitwiseNot((int)(string)$targetType->numberRange->min->value)
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
