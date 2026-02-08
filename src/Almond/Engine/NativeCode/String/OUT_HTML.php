<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue> */
final readonly class OUT_HTML extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, NullType $parameterType): StringType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, NullValue $parameter): StringValue {
			echo nl2br(htmlspecialchars($target->literalValue));
			return $target;
		};
	}

}
