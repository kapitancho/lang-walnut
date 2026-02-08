<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, StringType, StringValue, StringValue> */
final readonly class Contains extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, StringType $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, StringValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(
				str_contains($target->literalValue, $parameter->literalValue)
			);
	}

}
