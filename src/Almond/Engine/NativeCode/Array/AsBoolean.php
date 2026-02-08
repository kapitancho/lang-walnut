<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

final readonly class AsBoolean extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, NullType $parameterType): BooleanType => $this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(TupleValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(count($target->values) > 0);
	}

}