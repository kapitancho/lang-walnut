<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class AsBoolean extends NativeMethod {

	protected function getValidator(): callable {
		return fn(IntegerType $targetType, NullType $parameterType): BooleanType|TrueType|FalseType =>
			match(true) {
				(string)$targetType->numberRange === '0' => $this->typeRegistry->false,
				$targetType->numberRange->min !== MinusInfinity::value && (
					$targetType->numberRange->min->value > '0' || (
						$targetType->numberRange->min->value == '0' && !$targetType->numberRange->min->inclusive
					)
				),
					$targetType->numberRange->max !== PlusInfinity::value && (
						$targetType->numberRange->max->value < '0' || (
							$targetType->numberRange->max->value == '0' && !$targetType->numberRange->max->inclusive
						)
					) => $this->typeRegistry->true,
				default => $this->typeRegistry->boolean,
			};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean((string)$target->literalValue !== '0');
	}
}
