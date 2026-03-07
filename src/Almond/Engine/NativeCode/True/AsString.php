<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\True;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\AsString as AsStringInterface;

final readonly class AsString extends AsStringInterface {

	protected function getValidator(): callable {
		return fn(TrueType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->stringSubset(['true']);
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string('true');
	}

}
