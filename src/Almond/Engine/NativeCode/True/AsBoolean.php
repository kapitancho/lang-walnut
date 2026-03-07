<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\True;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\NativeCode\Boolean\AsBoolean as AsBooleanInterface;

final readonly class AsBoolean extends AsBooleanInterface {

	protected function getValidator(): callable {
		return fn(TrueType $targetType, NullType $parameterType): TrueType =>
			$this->typeRegistry->true;
	}

}
