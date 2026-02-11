<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerType, NullValue> */
final readonly class AsJsonValue extends NativeMethod {

	protected function getValidator(): callable {
		return fn(IntegerType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->typeByName(new TypeName('JsonValue'));
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): IntegerValue =>
			$target;
	}

}
