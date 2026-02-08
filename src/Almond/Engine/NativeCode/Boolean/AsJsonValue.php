<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BooleanType, NullType, BooleanValue, NullValue> */
final readonly class AsJsonValue extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BooleanType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->typeByName(new TypeName('JsonValue'));
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, NullValue $parameter): BooleanValue =>
			$target;
	}

}
