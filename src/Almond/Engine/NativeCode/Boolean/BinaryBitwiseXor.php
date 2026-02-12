<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BooleanType|TrueType|FalseType, BooleanType|TrueType|FalseType, BooleanValue, BooleanValue> */
final readonly class BinaryBitwiseXor extends NativeMethod {

	protected function getValidator(): callable {
		return fn(
			BooleanType|TrueType|FalseType $targetType,
			BooleanType|TrueType|FalseType $parameterType
		): BooleanType|TrueType|FalseType => match(true) {
			($targetType instanceof FalseType && $parameterType instanceof FalseType) ||
			($targetType instanceof TrueType && $parameterType instanceof TrueType) => $this->typeRegistry->false,
			($targetType instanceof FalseType && $parameterType instanceof TrueType) ||
			($targetType instanceof TrueType && $parameterType instanceof FalseType) => $this->typeRegistry->true,
			default => $this->typeRegistry->boolean
		};
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, BooleanValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(
				($target->literalValue && !$parameter->literalValue) ||
				(!$target->literalValue && $parameter->literalValue)
			);
	}

}
