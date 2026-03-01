<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\False;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\AsString as AsStringInterface;

final readonly class AsString extends AsStringInterface {

	protected function getValidator(): callable {
		return fn(FalseType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->stringSubset(['false']);
	}

}
