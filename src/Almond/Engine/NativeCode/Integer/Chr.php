<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class Chr extends NativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		return $targetType instanceof IntegerType &&
			$targetType->numberRange->min instanceof NumberIntervalEndpoint &&
			$targetType->numberRange->min->value >= 0 &&
			$targetType->numberRange->min->value <= 255 ?
				null :
				sprintf(
					"The 'chr' method requires the target to be an integer in the range 0..255, but %s was given.",
					$targetType
				);
	}

	protected function getValidator(): callable {
		return fn(IntegerType $targetType, NullType $parameterType): BytesType =>
			$this->typeRegistry->bytes(1, 1);
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): BytesValue =>
			$this->valueRegistry->bytes(
				chr(
					/** @phpstan-ignore argument.type */
					(int)(string)$target->literalValue
				)
			);
	}
}
