<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Open;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value as ValueInterface;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<OpenType|MetaType, NullType, OpenValue, NullValue> */
final readonly class Value extends NativeMethod {

	protected function getValidator(): callable {
		return fn(OpenType|MetaType $targetType, NullType $parameterType, mixed $origin): Type =>
			$targetType instanceof MetaType ?
				$this->typeRegistry->any :
				$targetType->valueType;
	}

	protected function getExecutor(): callable {
		return fn(OpenValue $target, NullValue $parameter): ValueInterface => $target->value;
	}
}