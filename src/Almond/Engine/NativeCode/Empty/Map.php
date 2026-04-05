<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Empty;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EmptyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

final readonly class Map extends NativeMethod {

	protected function getValidator(): callable {
		return fn(EmptyType $targetType, FunctionType $parameterType, mixed $origin): EmptyType => $targetType;
	}

	protected function getExecutor(): callable {
		return fn(EmptyValue $target, Value $parameter): EmptyValue => $target;
	}

}
