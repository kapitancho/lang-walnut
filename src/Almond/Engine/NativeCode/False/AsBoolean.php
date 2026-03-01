<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\False;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\AsBoolean as AsBooleanInterface;

final readonly class AsBoolean extends AsBooleanInterface {

	protected function getValidator(): callable {
		return fn(FalseType $targetType, NullType $parameterType): FalseType =>
			$this->typeRegistry->false;
	}

}
