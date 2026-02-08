<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Null;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<NullType, NullType, NullValue, NullValue> */
final readonly class AsString extends NativeMethod {

	protected function getValidator(): callable {
		return fn(NullType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->stringSubset(['null']);
	}

	protected function getExecutor(): callable {
		return fn(NullValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string('null');
	}

}
