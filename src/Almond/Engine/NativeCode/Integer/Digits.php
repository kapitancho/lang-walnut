<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class Digits extends NativeMethod {

	private function digitCount(Number $bound): Number|PlusInfinity {
		return new Number(strlen((string)$bound));
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool {
		return $targetType instanceof IntegerType &&
			$targetType->numberRange->min !== MinusInfinity::value &&
			$targetType->numberRange->min->value >= '0';
	}

	protected function getValidator(): callable {
		return function(IntegerType $targetType, NullType $parameterType): ArrayType {
			$minLength = $this->digitCount($targetType->numberRange->min->value);
			$maxLength = $targetType->numberRange->max === PlusInfinity::value ? null : $this->digitCount($targetType->numberRange->max->value);

			return $this->typeRegistry->array(
				$targetType,
				$minLength,
				$maxLength ?? PlusInfinity::value
			);
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue $target, NullValue $parameter): TupleValue {
			$value = (int)(string)$target->literalValue;
			$valueStr = (string)$value;
			$digits = [];

			for ($i = 0; $i < strlen($valueStr); $i++) {
				$digits[] = $this->valueRegistry->integer((int)$valueStr[$i]);
			}

			return $this->valueRegistry->tuple($digits);
		};
	}
}
