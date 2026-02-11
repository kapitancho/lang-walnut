<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<\Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type, TypeType, TypeValue> */
final readonly class BinaryLessThanEqual extends TypeNativeMethod {

	protected function getValidator(): callable {
		return fn(TypeType $targetType, TypeType $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(TypeValue $target, TypeValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean($target->typeValue->isSubtypeOf($parameter->typeValue));
	}

}
