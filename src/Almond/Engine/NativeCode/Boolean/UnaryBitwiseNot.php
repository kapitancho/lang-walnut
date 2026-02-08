<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BooleanType|TrueType|FalseType, NullType, BooleanValue, NullValue> */
final readonly class UnaryBitwiseNot extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BooleanType|TrueType|FalseType $targetType, NullType $parameterType): Type =>
			match(true) {
				$targetType instanceof FalseType => $this->typeRegistry->true,
				$targetType instanceof TrueType => $this->typeRegistry->false,
				default => $this->typeRegistry->boolean
			};
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(!$target->literalValue);
	}

}
