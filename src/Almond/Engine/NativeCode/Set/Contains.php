<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<Type, Value> */
final readonly class Contains extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, Type $parameterType): BooleanType|FalseType =>
			$parameterType->isSubtypeOf($targetType->itemType) ?
				$this->typeRegistry->boolean :
				$this->typeRegistry->false;
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, Value $parameter): BooleanValue =>
			$this->valueRegistry->boolean(
				array_key_exists((string)$parameter, $target->valueSet)
			);
	}

}
