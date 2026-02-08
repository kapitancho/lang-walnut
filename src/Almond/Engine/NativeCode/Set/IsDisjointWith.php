<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<AnyType, SetType, SetValue> */
final readonly class IsDisjointWith extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, SetType $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, SetValue $parameter): Value =>
			$this->valueRegistry->boolean(
				count(array_intersect(
					array_keys($target->valueSet),
					array_keys($parameter->valueSet),
				)) === 0
			);
	}

}
