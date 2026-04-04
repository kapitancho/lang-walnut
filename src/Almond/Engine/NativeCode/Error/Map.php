<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Error;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

final readonly class Map extends NativeMethod {

	protected function getValidator(): callable {
		return fn(ErrorType $targetType, FunctionType $parameterType, mixed $origin): ErrorType => $targetType;
	}

	protected function getExecutor(): callable {
		return fn(ErrorValue $target, Value $parameter): ErrorValue => $target;
	}

}
