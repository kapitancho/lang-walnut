<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Result;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ResultType, NullType, ErrorValue, NullValue> */
final readonly class Error extends NativeMethod {

	protected function getValidator(): callable {
		return fn(ResultType $targetType, NullType $parameterType): Type =>
			$targetType->errorType;
	}

	protected function getExecutor(): callable {
		return fn(ErrorValue $target, NullValue $parameter): Value =>
			$target->errorValue;
	}

}
